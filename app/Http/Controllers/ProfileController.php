<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HandlesDocumentDownloads;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Document;
use App\Models\DocumentObligatoire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use HandlesDocumentDownloads;

    /**
     * Taille maximale autorisée pour les documents obligatoires (en kilo-octets).
     *
     * 8192 KB = 8 MB. Cette limite respecte les recommandations de sécurité SonarQube
     * (limite recommandée : inférieure ou égale à 8 MB pour les uploads de fichiers).
     * Cette limite est choisie pour :
     * - limiter l'impact mémoire/disque des uploads,
     * - rester suffisante pour les documents administratifs usuels (PDF, images),
     * - réduire les risques d'attaque par upload de fichiers trop volumineux.
     */
    private const MAX_DOCUMENT_SIZE_KB = 8192;       // 8 MB - Compliant avec les recommandations SonarQube
    private const ZIP_MAGIC_BYTES      = '504b0304'; // Magic bytes pour fichiers ZIP/DOCX

    /**
     * Methode pour afficher le formulaire de profil de l'utilisateur
     * @param Request $request la requête HTTP contenant les informations de l'utilisateur
     * @return View la vue du formulaire de profil avec les données nécessaires
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['familles.enfants.classe']);

        // Charger les documents obligatoires pour les rôles de l'utilisateur
        $userRoleIds = $user->rolesCustom()->pluck('avoir.idRole')->toArray();

        if (empty($userRoleIds)) {
            $documentsObligatoiresAvecEtat = collect([]);
        } else {
            $documentsObligatoires = DocumentObligatoire::whereHas('roles', function ($query) use ($userRoleIds) {
                $query->whereIn('attribuer.idRole', $userRoleIds);
            })->get();

            // Pour chaque document obligatoire, trouver les documents uploadés par l'utilisateur
            // On utilise le nom du document obligatoire pour faire le lien
            $documentsObligatoiresAvecEtat = $documentsObligatoires->map(function ($docOblig) use ($user) {
                // Récupérer tous les documents de l'utilisateur qui correspondent au nom du document obligatoire
                $documentsUtilisateur = $user->documents()
                    ->where(function ($query) use ($docOblig) {
                        $query->where('nom', 'like', '%' . $docOblig->nom . '%')
                            ->orWhere('nom', $docOblig->nom);
                    })
                    ->orderBy('idDocument', 'desc')
                    ->get();

                // Prendre le dernier document (le plus récent)
                $dernierDocument = $documentsUtilisateur->first();

                if (! $dernierDocument) {
                    $docOblig->etat             = 'non_remis';
                    $docOblig->documentUploaded = null;
                } else {
                    // Mapper les états : actif = remis, en_attente = en_cours_validation, valide = valide
                    $etatMapping = [
                        'actif'      => 'remis',
                        'en_attente' => 'en_cours_validation',
                        'valide'     => 'valide',
                    ];
                    $docOblig->etat             = $etatMapping[$dernierDocument->etat] ?? 'remis';
                    $docOblig->documentUploaded = $dernierDocument;
                }

                return $docOblig;
            });
        }

        return view('profile.edit', [
            'user'                  => $user,
            'documentsObligatoires' => $documentsObligatoiresAvecEtat,
        ]);
    }

    /**
     * Methode pour mettre à jour les informations du profil de l'utilisateur
     * @param ProfileUpdateRequest $request la requête de mise à jour du profil
     * @return RedirectResponse la réponse de redirection après la mise à jour
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Update fields
        $user->fill($request->only(['nom', 'email', 'prenom', 'date_naissance', 'photo']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Methode pour supprimer le compte de l'utilisateur
     * @param Request $request la requête HTTP contenant les informations de suppression
     * @return RedirectResponse la réponse de redirection vers la page d'accueil après la suppression
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Méthode pour uploader un document obligatoire dans le profil de l'utilisateur
     * @param Request $request la requête HTTP contenant le fichier à uploader et l'identifiant du document obligatoire
     * @return RedirectResponse la réponse de redirection vers le formulaire de profil avec un message de succès ou d'erreur selon le résultat de l'upload
     */
    public function uploadDocument(Request $request): RedirectResponse
    {
        $path     = null;
        $response = null;

        try {
            $this->validateDocumentUpload($request);
            $user                  = $request->user();
            $file                  = $request->file('document');
            $idDocumentObligatoire = $request->input('idDocumentObligatoire');

            $documentObligatoire = $this->getDocumentObligatoireForUser($user, $idDocumentObligatoire);
            $response            = $this->checkDocumentUploadability($user, $documentObligatoire);

            if (! $response) {
                $extension = strtolower($file->getClientOriginalExtension());
                $response  = $this->validateFileExtension($extension);
            }

            if (! $response) {
                $response = $this->validateFileMagicBytesWrapper($file, $extension);
            }

            if (! $response) {
                $path = $this->storeDocument($file, $user);
                $this->attachDocumentToUser($user, $path, $documentObligatoire, $file, $extension);
                $response = Redirect::route('profile.edit')->with('status', 'document-uploaded');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $response = $this->handleUploadError($e->errors(), $path);
        } catch (\Exception $e) {
            $response = $this->handleUploadError($e->getMessage(), $path);
        }

        return $response;
    }
    /**
     * Méthode pour gérer les erreurs d'upload de document, en supprimant le fichier uploadé en cas d'erreur et en redirigeant vers le formulaire de profil avec un message d'erreur approprié
     * @param string|array $error le message d'erreur ou les erreurs de validation à afficher
     * @param string|null $path le chemin du fichier uploadé à supprimer en cas d'erreur (optionnel)
     * @return RedirectResponse la réponse de redirection vers le formulaire de profil avec le message d'erreur
     */
    private function handleUploadError($error, ?string $path = null): RedirectResponse
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        if (is_array($error)) {
            return Redirect::route('profile.edit')
                ->withErrors($error)
                ->with('error', __('auth.upload_error'));
        }

        return Redirect::route('profile.edit')
            ->with('error', __('auth.upload_error') . ': ' . $error);
    }

    /**
     * Méthode pour valider les données d'upload de document, en vérifiant la présence du fichier, sa taille, son type et l'existence du document obligatoire correspondant
     * @param Request $request la requête HTTP contenant les données d'upload
     * @return void
     */
    private function validateDocumentUpload(Request $request): void
    {
        $maxSize = (int) self::MAX_DOCUMENT_SIZE_KB;
        $request->validate([
            'document'              => [
                'required',
                'file',
                'max:' . $maxSize,
                'mimes:pdf,doc,docx,jpg,jpeg,png',
            ],
            'idDocumentObligatoire' => ['required', 'integer', 'exists:documentObligatoire,idDocumentObligatoire'],
        ], [
            'document.required' => __('auth.document_required'),
            'document.file'     => __('auth.document_must_be_file'),
            'document.max'      => __('auth.document_size_exceeded', ['max' => '8']),
            'document.mimes'    => __('auth.document_invalid_format'),
        ]);
    }

    /**
     * Méthode pour récupérer le document obligatoire correspondant à l'utilisateur et à l'identifiant du document obligatoire, en vérifiant que le document est bien lié à un rôle de l'utilisateur
     * @param $user l'utilisateur pour lequel on veut récupérer le document obligatoire
     * @param int $idDocumentObligatoire l'identifiant du document obligatoire à récupérer
     * @return DocumentObligatoire le document obligatoire correspondant à l'utilisateur et à l'identifiant fourni
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException si aucun document obligatoire correspondant n'est trouvé pour l'utilisateur et l'identifiant fourni
     */
    private function getDocumentObligatoireForUser($user, int $idDocumentObligatoire): DocumentObligatoire
    {
        $userRoleIds = $user->rolesCustom()->pluck('avoir.idRole')->toArray();
        return DocumentObligatoire::whereHas('roles', function ($query) use ($userRoleIds) {
            $query->whereIn('attribuer.idRole', $userRoleIds);
        })->findOrFail($idDocumentObligatoire);
    }

    /**
     * Méthode pour vérifier si un document peut être uploadé pour un document obligatoire donné, en vérifiant l'existence d'un document déjà uploadé par l'utilisateur avec le même nom de document obligatoire et un état "en_attente" ou "valide"
     * @param $user l'utilisateur pour lequel on veut vérifier la possibilité d'upload du document
     * @param DocumentObligatoire $documentObligatoire le document obligatoire pour lequel on veut vérifier la possibilité d'upload du document
     * @return RedirectResponse|null une réponse de redirection vers le formulaire de profil avec un message d'erreur si un document non uploadable est trouvé, ou null si le document peut être uploadé
     */
    private function checkDocumentUploadability($user, DocumentObligatoire $documentObligatoire): ?RedirectResponse
    {
        $dernierDocument = $user->documents()
            ->where(function ($query) use ($documentObligatoire) {
                $query->where('nom', 'like', '%' . $documentObligatoire->nom . '%')
                    ->orWhere('nom', $documentObligatoire->nom);
            })
            ->orderBy('idDocument', 'desc')
            ->first();

        if ($dernierDocument && in_array($dernierDocument->etat, ['en_attente', 'valide'])) {
            return Redirect::route('profile.edit')
                ->with('error', __('auth.document_non_uploadable'));
        }
        return null;
    }

    /**
     * Méthode pour valider l'extension d'un fichier uploadé, en vérifiant que l'extension est bien dans la liste des extensions autorisées
     * @param string $extension l'extension du fichier à valider
     * @return RedirectResponse|null une réponse de redirection vers le formulaire de profil avec un message d'erreur si l'extension n'est pas autorisée, ou null si l'extension est valide
     */
    private function validateFileExtension(string $extension): ?RedirectResponse
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (! in_array($extension, $allowedExtensions)) {
            return Redirect::route('profile.edit')
                ->with('error', __('auth.invalid_file_format'));
        }
        return null;
    }

    /**
     * Méthode pour valider le format d'un fichier uploadé en analysant les premiers octets (magic bytes), en vérifiant que les magic bytes correspondent à l'extension du fichier et que le contenu d'un fichier DOCX est valide
     * @param $file le fichier à valider
     * @param string $extension l'extension du fichier à valider
     * @return RedirectResponse|null une réponse de redirection vers le formulaire de profil avec un message d'erreur si le format du fichier est invalide, ou null si le format est valide
     * @throws \Exception si une erreur survient lors de la validation du format du fichier
     */
    private function validateFileMagicBytesWrapper($file, string $extension): ?RedirectResponse
    {
        $magicBytesValidation = $this->validateFileMagicBytes($file, $extension);
        if (! $magicBytesValidation['valid']) {
            return Redirect::route('profile.edit')
                ->with('error', $magicBytesValidation['message'] ?? __('auth.invalid_file_format'));
        }
        return null;
    }

    /**
     * Méthode pour stocker un fichier uploadé dans le système de fichiers, en utilisant un chemin organisé par utilisateur et par type de document, et en retournant le chemin de stockage du fichier
     * @param $file le fichier à stocker
     * @param $user l'utilisateur pour lequel on veut stocker le fichier
     * @return string le chemin de stockage du fichier après l'avoir stocké
     */
    private function storeDocument($file, $user): string
    {
        return $file->store('profiles/' . $user->idUtilisateur . '/obligatoires', 'public');
    }
    /**
     * Méthode pour attacher un document à un utilisateur, en créant une entrée dans la table des documents avec les informations du fichier et du document obligatoire, et en attachant le document à l'utilisateur dans la table pivot
     * @param $user l'utilisateur auquel on veut attacher le document
     * @param string $path le chemin de stockage du fichier à attacher
     * @param DocumentObligatoire $documentObligatoire le document obligatoire correspondant au document à attacher
     * @param $file le fichier à attacher
     * @param string $extension l'extension du fichier à attacher
     * @return void
     */
    private function attachDocumentToUser($user, string $path, DocumentObligatoire $documentObligatoire, $file, string $extension): void
    {
        $type       = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'doc';
        $nomFichier = $file->getClientOriginalName();
        $nomComplet = $documentObligatoire->nom . ' - ' . $nomFichier;
        $nomFinal   = strlen($nomComplet) > 50 ? substr($nomComplet, 0, 47) . '...' : $nomComplet;

        $document = Document::create([
            'nom'                   => $nomFinal,
            'chemin'                => $path,
            'type'                  => $type,
            'etat'                  => 'en_attente',
            'idDocumentObligatoire' => $documentObligatoire->idDocumentObligatoire,
        ]);

        $user->documents()->attach($document->idDocument);
    }

    /**
     * Valide le format d'un fichier en analysant les premiers octets (magic bytes)
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $extension
     * @return array ['valid' => bool, 'message' => string|null]
     * @throws \Exception si une erreur survient lors de la validation du format du fichier
     */
    private function validateFileMagicBytes($file, string $extension): array
    {
        $result = ['valid' => false, 'message' => null];

        try {
            $hex = $this->readFileHex($file);
            if (! $hex) {
                $result['message'] = __('auth.cannot_read_file');
            } else {
                $magicBytes = $this->getMagicBytesForExtension($extension);
                if (! $magicBytes) {
                    $result['message'] = __('auth.unsupported_file_type');
                } else {
                    $valid             = $this->checkMagicBytes($hex, $magicBytes);
                    $valid             = $this->validateDocxIfNeeded($file, $extension, $hex, $valid);
                    $result['valid']   = $valid;
                    $result['message'] = $valid ? null : __('auth.file_type_mismatch', ['expected' => strtoupper($extension)]);
                }
            }
        } catch (\Exception $e) {
            $result['message'] = __('auth.file_validation_error');
        }

        return $result;
    }

    /**
     * Lit les premiers octets d'un fichier et les convertit en une chaîne hexadécimale
     * @param $file le fichier à lire
     * @return string|null la chaîne hexadécimale des premiers octets du fichier, ou null si une erreur survient lors de la lecture du fichier
     */
    private function readFileHex($file): ?string
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            return null;
        }

        $bytes = fread($handle, 12);
        fclose($handle);

        if ($bytes === false || strlen($bytes) < 4) {
            return null;
        }

        return bin2hex($bytes);
    }

    /**
     * Retourne les magic bytes attendus pour une extension de fichier donnée, ou null si l'extension n'est pas reconnue
     * @param string $extension l'extension du fichier pour laquelle on veut obtenir les magic bytes
     * @return array|null un tableau de chaînes hexadécimales représentant les magic bytes attendus pour l'extension donnée, ou null si l'extension n'est pas reconnue
     */
    private function getMagicBytesForExtension(string $extension): ?array
    {
        $magicBytes = [
            'pdf'  => ['25504446'],             // %PDF
            'jpg'  => ['ffd8ff'],               // JPEG
            'jpeg' => ['ffd8ff'],               // JPEG
            'png'  => ['89504e47'],             // PNG
            'doc'  => ['d0cf11e0', '0d444f43'], // MS Office (DOC, XLS, PPT)
            'docx' => [self::ZIP_MAGIC_BYTES],  // ZIP/Office Open XML (DOCX, XLSX, PPTX)
        ];

        return $magicBytes[$extension] ?? null;
    }

    /**
     * Valide qu'un fichier DOCX contient bien un dossier word/ pour éviter les faux positifs de fichiers ZIP non valides
     * @param $file le fichier à valider
     * @param string $extension l'extension du fichier à valider
     * @param string $hex la chaîne hexadécimale des premiers octets du fichier
     * @param bool $valid le résultat de la validation des magic bytes avant de vérifier le contenu du DOCX
     * @return bool true si le fichier est valide, false sinon
     */
    private function validateDocxIfNeeded($file, string $extension, string $hex, bool $valid): bool
    {
        if ($extension !== 'docx' || $valid || strpos($hex, self::ZIP_MAGIC_BYTES) !== 0) {
            return $valid;
        }

        if (class_exists('ZipArchive')) {
            return $this->validateDocxZip($file);
        }

        return true;
    }

    /**
     * Vérifie que les premiers octets d'un fichier correspondent à l'un des magic bytes attendus
     * @param string $hex la chaîne hexadécimale des premiers octets du fichier
     * @param array $magicBytesList un tableau de chaînes hexadécimales représentant les magic bytes attendus pour le type de fichier
     * @return bool true si les premiers octets du fichier correspondent à l'un des magic bytes attendus, false sinon
     */
    private function checkMagicBytes(string $hex, array $magicBytesList): bool
    {
        foreach ($magicBytesList as $magicHex) {
            if (strpos($hex, $magicHex) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Valide qu'un fichier DOCX est un ZIP valide contenant un dossier word/, pour éviter les faux positifs de fichiers ZIP non valides qui pourraient être uploadés avec une extension .docx
     * @param $file le fichier à valider
     * @return bool true si le fichier est un ZIP valide contenant un dossier word/, false sinon
     */
    private function validateDocxZip($file): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            return false;
        }

        $hasWord = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (strpos($zip->getNameIndex($i), 'word/') === 0) {
                $hasWord = true;
                break;
            }
        }
        $zip->close();
        return $hasWord;
    }

    /**
     * Méthode pour supprimer un document du profil de l'utilisateur, en vérifiant que le document appartient bien à l'utilisateur et qu'il n'est pas validé, puis en supprimant le fichier physique, en détachant le document de l'utilisateur dans la table pivot, et en supprimant le document si aucun autre utilisateur ne l'utilise
     * @param Request $request la requête HTTP contenant les informations de l'utilisateur et du document à supprimer
     * @param Document $document le document à supprimer
     * @return RedirectResponse la réponse de redirection vers le formulaire de profil avec un message de succès ou d'erreur selon le résultat de la suppression
     */
    public function deleteDocument(Request $request, Document $document): RedirectResponse
    {
        $user = $request->user();

        // Vérifier que le document appartient à l'utilisateur
        if (! $user->documents()->where('document.idDocument', $document->idDocument)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Ne pas permettre la suppression si le document est validé
        if ($document->etat === 'valide') {
            return Redirect::route('profile.edit')
                ->with('error', __('auth.document_validated_cannot_delete'));
        }

        // Supprimer le fichier physique
        if (Storage::disk('public')->exists($document->chemin)) {
            Storage::disk('public')->delete($document->chemin);
        }

        // Détacher le document de l'utilisateur
        $user->documents()->detach($document->idDocument);

        // Supprimer le document si aucun autre utilisateur ne l'utilise
        if ($document->utilisateurs()->count() === 0) {
            $document->delete();
        }

        return Redirect::route('profile.edit')->with('status', 'document-deleted');
    }

    /**
     * Méthode pour télécharger un document du profil de l'utilisateur
     */
    public function downloadDocument(Request $request, Document $document)
    {
        $user = $request->user();

        return $this->downloadDocumentWithFormattedName($user, $document);
    }
}