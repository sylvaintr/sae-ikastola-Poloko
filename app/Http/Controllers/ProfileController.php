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
}
