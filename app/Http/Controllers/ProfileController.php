<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Document;
use App\Models\DocumentObligatoire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
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
                    ->where('nom', 'like', '%' . $docOblig->nom . '%')
                    ->orWhere('nom', $docOblig->nom)
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
        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'idDocumentObligatoire' => ['required', 'integer', 'exists:documentObligatoire,idDocumentObligatoire'],
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
        
        // Déterminer le type de fichier
        $extension = strtolower($file->getClientOriginalExtension());
        $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'document';
        
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
    }

    /**
     * Méthode pour supprimer un document du profil de l'utilisateur
     */
    public function deleteDocument(Request $request, Document $document): RedirectResponse
    {
        $user = $request->user();
        
        // Vérifier que le document appartient à l'utilisateur
        if (!$user->documents()->where('idDocument', $document->idDocument)->exists()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Ne pas permettre la suppression si le document est en cours de validation ou validé
        if (in_array($document->etat, ['en_attente', 'valide'])) {
            return Redirect::route('profile.edit')
                ->with('error', __('auth.document_non_uploadable'));
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
}
