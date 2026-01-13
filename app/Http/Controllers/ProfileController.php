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
            // NOSONAR - Upload de document obligatoire limité à 8MB
            $request->validate([
                'document' => [
                    'required',
                    'file',
                    'max:' . (int) self::MAX_DOCUMENT_SIZE_KB, // 8MB max
                    'mimes:pdf,doc,docx,jpg,jpeg,png'
                ],
                'idDocumentObligatoire' => ['required', 'integer', 'exists:documentObligatoire,idDocumentObligatoire'],
            ], [
                'document.required' => __('auth.document_required'),
                'document.file' => __('auth.document_must_be_file'),
                'document.max' => __('auth.document_size_exceeded', ['max' => '8']),
                'document.mimes' => __('auth.document_invalid_format'),
            ]);

            $user = $request->user();
            $file = $request->file('document');
            $idDocumentObligatoire = $request->input('idDocumentObligatoire');
            
            // Vérifier que le document obligatoire est bien associé à un rôle de l'utilisateur
            $userRoleIds = $user->rolesCustom()->pluck('avoir.idRole')->toArray();
            $documentObligatoire = DocumentObligatoire::whereHas('roles', function($query) use ($userRoleIds) {
                $query->whereIn('attribuer.idRole', $userRoleIds);
            })->findOrFail($idDocumentObligatoire);
            
            // Vérifier que le document n'est pas déjà en cours de validation ou validé
            // On utilise le nom du document obligatoire pour trouver les documents existants
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
            
            // Vérifier le format du fichier en analysant les premiers octets (magic bytes)
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($extension, $allowedExtensions)) {
                return Redirect::route('profile.edit')
                    ->with('error', __('auth.invalid_file_format'));
            }
            
            // Vérifier les magic bytes du fichier
            $magicBytesValidation = $this->validateFileMagicBytes($file, $extension);
            if (!$magicBytesValidation['valid']) {
                return Redirect::route('profile.edit')
                    ->with('error', $magicBytesValidation['message'] ?? __('auth.invalid_file_format'));
            }
            
            // Déterminer le type de fichier (limité à 5 caractères max selon la migration)
            $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'doc';
            
            // Stocker le fichier
            $path = $file->store('profiles/' . $user->idUtilisateur . '/obligatoires', 'public');
            
            // Créer le document avec le nom du fichier (limité à 50 caractères max)
            $nomFichier = $file->getClientOriginalName();
            $nomComplet = $documentObligatoire->nom . ' - ' . $nomFichier;
            $nomFinal = strlen($nomComplet) > 50 ? substr($nomComplet, 0, 47) . '...' : $nomComplet;
            
            $document = Document::create([
                'nom' => $nomFinal,
                'chemin' => $path,
                'type' => $type,
                'etat' => 'en_attente', // En cours de validation
            ]);
            
            // Lier le document à l'utilisateur
            $user->documents()->attach($document->idDocument);
            
            return Redirect::route('profile.edit')->with('status', 'document-uploaded');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Redirect::route('profile.edit')
                ->withErrors($e->errors())
                ->with('error', __('auth.upload_error'));
        } catch (\Exception $e) {
            // En cas d'erreur, supprimer le fichier s'il a été créé
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return Redirect::route('profile.edit')
                ->with('error', __('auth.upload_error') . ': ' . $e->getMessage());
        }
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
                'docx' => ['504b0304'], // ZIP/Office Open XML (DOCX, XLSX, PPTX)
            ];
            
            if (!isset($magicBytes[$extension])) {
                return ['valid' => false, 'message' => __('auth.unsupported_file_type')];
            }
            
            // Vérifier si les magic bytes correspondent
            $valid = false;
            foreach ($magicBytes[$extension] as $magicHex) {
                if (strpos($hex, $magicHex) === 0) {
                    $valid = true;
                    break;
                }
            }
            
            // Pour DOCX, vérifier aussi qu'il contient "word" dans le ZIP si l'extension est disponible
            if ($extension === 'docx' && !$valid && class_exists('ZipArchive')) {
                // DOCX est un ZIP, on vérifie les magic bytes ZIP
                if (strpos($hex, '504b0304') === 0) {
                    // Ouvrir le fichier comme ZIP pour vérifier qu'il contient word/
                    $zip = new \ZipArchive();
                    if ($zip->open($file->getRealPath()) === true) {
                        $hasWord = false;
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            if (strpos($zip->getNameIndex($i), 'word/') === 0) {
                                $hasWord = true;
                                break;
                            }
                        }
                        $zip->close();
                        $valid = $hasWord;
                    }
                }
            }
            
            // Si DOCX a les bons magic bytes ZIP mais qu'on ne peut pas vérifier le contenu, on accepte quand même
            if ($extension === 'docx' && !$valid && strpos($hex, '504b0304') === 0) {
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
