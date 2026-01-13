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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
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
    private const MAX_DOCUMENT_SIZE_KB = 8192; // 8 MB - Compliant avec les recommandations SonarQube
    private const ZIP_MAGIC_BYTES = '504b0304'; // Magic bytes pour fichiers ZIP/DOCX
    private const REGEX_CLEAN_FILENAME = '/[^a-zA-Z0-9]/'; // Regex pour nettoyer les noms de fichiers

    /**
     * Methode pour afficher le formulaire de profil de l'utilisateur
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['familles.enfants.classe']);
        
        // Charger les documents obligatoires pour les rôles de l'utilisateur
        $userRoleIds = $user->rolesCustom()->pluck('avoir.idRole')->toArray();
        
        if (empty($userRoleIds)) {
            $documentsObligatoiresAvecEtat = collect([]);
        } else {
            $documentsObligatoires = DocumentObligatoire::whereHas('roles', function($query) use ($userRoleIds) {
                $query->whereIn('attribuer.idRole', $userRoleIds);
            })->get();
            
            // Pour chaque document obligatoire, trouver les documents uploadés par l'utilisateur
            // On utilise le nom du document obligatoire pour faire le lien
            $documentsObligatoiresAvecEtat = $documentsObligatoires->map(function($docOblig) use ($user) {
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
                
                if (!$dernierDocument) {
                    $docOblig->etat = 'non_remis';
                    $docOblig->documentUploaded = null;
                } else {
                    // Mapper les états : actif = remis, en_attente = en_cours_validation, valide = valide
                    $etatMapping = [
                        'actif' => 'remis',
                        'en_attente' => 'en_cours_validation',
                        'valide' => 'valide'
                    ];
                    $docOblig->etat = $etatMapping[$dernierDocument->etat] ?? 'remis';
                    $docOblig->documentUploaded = $dernierDocument;
                }
                
                return $docOblig;
            });
        }
        
        return view('profile.edit', [
            'user' => $user,
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
     * @return RedirectResponse la réponse de redirection après la suppression
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
     */
    public function uploadDocument(Request $request): RedirectResponse
    {
        try {
            $this->validateDocumentUpload($request);
            $user = $request->user();
            $file = $request->file('document');
            $idDocumentObligatoire = $request->input('idDocumentObligatoire');

            $documentObligatoire = $this->getDocumentObligatoireForUser($user, $idDocumentObligatoire);
            $error = $this->checkDocumentUploadability($user, $documentObligatoire);
            if ($error) {
                return $error;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $error = $this->validateFileExtension($extension);
            if ($error) {
                return $error;
            }

            $error = $this->validateFileMagicBytesWrapper($file, $extension);
            if ($error) {
                return $error;
            }

            $path = $this->storeDocument($file, $user, $documentObligatoire);
            $this->attachDocumentToUser($user, $path, $documentObligatoire, $file, $extension);

            return Redirect::route('profile.edit')->with('status', 'document-uploaded');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return Redirect::route('profile.edit')
                ->withErrors($e->errors())
                ->with('error', __('auth.upload_error'));
        } catch (\Exception $e) {
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return Redirect::route('profile.edit')
                ->with('error', __('auth.upload_error') . ': ' . $e->getMessage());
        }
    }

    private function validateDocumentUpload(Request $request): void
    {
        $maxSize = (int) self::MAX_DOCUMENT_SIZE_KB;
        $request->validate([
            'document' => [
                'required',
                'file',
                'max:' . $maxSize,
                'mimes:pdf,doc,docx,jpg,jpeg,png'
            ],
            'idDocumentObligatoire' => ['required', 'integer', 'exists:documentObligatoire,idDocumentObligatoire'],
        ], [
            'document.required' => __('auth.document_required'),
            'document.file' => __('auth.document_must_be_file'),
            'document.max' => __('auth.document_size_exceeded', ['max' => '8']),
            'document.mimes' => __('auth.document_invalid_format'),
        ]);
    }

    private function getDocumentObligatoireForUser($user, int $idDocumentObligatoire): DocumentObligatoire
    {
        $userRoleIds = $user->rolesCustom()->pluck('avoir.idRole')->toArray();
        return DocumentObligatoire::whereHas('roles', function($query) use ($userRoleIds) {
            $query->whereIn('attribuer.idRole', $userRoleIds);
        })->findOrFail($idDocumentObligatoire);
    }

    private function checkDocumentUploadability($user, DocumentObligatoire $documentObligatoire): ?RedirectResponse
    {
        $dernierDocument = $user->documents()
            ->where(function($query) use ($documentObligatoire) {
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

    private function validateFileExtension(string $extension): ?RedirectResponse
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedExtensions)) {
            return Redirect::route('profile.edit')
                ->with('error', __('auth.invalid_file_format'));
        }
        return null;
    }

    private function validateFileMagicBytesWrapper($file, string $extension): ?RedirectResponse
    {
        $magicBytesValidation = $this->validateFileMagicBytes($file, $extension);
        if (!$magicBytesValidation['valid']) {
            return Redirect::route('profile.edit')
                ->with('error', $magicBytesValidation['message'] ?? __('auth.invalid_file_format'));
        }
        return null;
    }

    private function storeDocument($file, $user, DocumentObligatoire $documentObligatoire): string
    {
        return $file->store('profiles/' . $user->idUtilisateur . '/obligatoires', 'public');
    }

    private function attachDocumentToUser($user, string $path, DocumentObligatoire $documentObligatoire, $file, string $extension): void
    {
        $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'doc';
        $nomFichier = $file->getClientOriginalName();
        $nomComplet = $documentObligatoire->nom . ' - ' . $nomFichier;
        $nomFinal = strlen($nomComplet) > 50 ? substr($nomComplet, 0, 47) . '...' : $nomComplet;

        $document = Document::create([
            'nom' => $nomFinal,
            'chemin' => $path,
            'type' => $type,
            'etat' => 'en_attente',
        ]);

        $user->documents()->attach($document->idDocument);
    }
    
    /**
     * Valide le format d'un fichier en analysant les premiers octets (magic bytes)
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $extension
     * @return array ['valid' => bool, 'message' => string|null]
     */
    private function validateFileMagicBytes($file, string $extension): array
    {
        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                return ['valid' => false, 'message' => __('auth.cannot_read_file')];
            }
            
            // Lire les premiers octets du fichier (suffisant pour identifier la plupart des formats)
            $bytes = fread($handle, 12);
            fclose($handle);
            
            if ($bytes === false || strlen($bytes) < 4) {
                return ['valid' => false, 'message' => __('auth.invalid_file_format')];
            }
            
            // Convertir les premiers octets en hexadécimal pour comparaison
            $hex = bin2hex($bytes);
            
            // Définir les magic bytes pour chaque type de fichier autorisé
            $magicBytes = [
                'pdf' => ['25504446'], // %PDF
                'jpg' => ['ffd8ff'], // JPEG
                'jpeg' => ['ffd8ff'], // JPEG
                'png' => ['89504e47'], // PNG
                'doc' => ['d0cf11e0', '0d444f43'], // MS Office (DOC, XLS, PPT)
                'docx' => [self::ZIP_MAGIC_BYTES], // ZIP/Office Open XML (DOCX, XLSX, PPTX)
            ];
            
            if (!isset($magicBytes[$extension])) {
                return ['valid' => false, 'message' => __('auth.unsupported_file_type')];
            }
            
            // Vérifier si les magic bytes correspondent
            $valid = $this->checkMagicBytes($hex, $magicBytes[$extension]);
            
            // Pour DOCX, vérifier aussi qu'il contient "word" dans le ZIP si l'extension est disponible
            if ($extension === 'docx' && !$valid && class_exists('ZipArchive') && strpos($hex, self::ZIP_MAGIC_BYTES) === 0) {
                $valid = $this->validateDocxZip($file);
            }
            
            // Si DOCX a les bons magic bytes ZIP mais qu'on ne peut pas vérifier le contenu, on accepte quand même
            if ($extension === 'docx' && !$valid && strpos($hex, self::ZIP_MAGIC_BYTES) === 0) {
                $valid = true;
            }
            
            if (!$valid) {
                return [
                    'valid' => false, 
                    'message' => __('auth.file_type_mismatch', ['expected' => strtoupper($extension)])
                ];
            }
            
            return ['valid' => true, 'message' => null];
            
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => __('auth.file_validation_error')];
        }
    }

    /**
     * Vérifie si les magic bytes correspondent
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
     * Valide qu'un fichier DOCX contient bien un dossier word/
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
     * Méthode pour supprimer un document du profil de l'utilisateur
     */
    public function deleteDocument(Request $request, Document $document): RedirectResponse
    {
        $user = $request->user();
        
        // Vérifier que le document appartient à l'utilisateur
        if (!$user->documents()->where('document.idDocument', $document->idDocument)->exists()) {
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
