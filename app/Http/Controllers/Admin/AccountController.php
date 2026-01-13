<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\DocumentObligatoire;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = Utilisateur::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtrage par rôle
        if ($request->filled('role')) {
            $roleId = $request->get('role');
            $query->whereHas('rolesCustom', function ($q) use ($roleId) {
                $q->where('role.idRole', $roleId);
            });
        }

        // Gestion du tri
        $sortColumn = $request->get('sort', 'nom');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSortColumns = ['prenom', 'nom', 'email', 'statutValidation', 'idUtilisateur', 'famille'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'nom';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Pour le tri par famille, on doit faire un join
        $baseSelect = [
            'utilisateur.idUtilisateur',
            'utilisateur.prenom',
            'utilisateur.nom',
            'utilisateur.email',
            'utilisateur.statutValidation',
            'utilisateur.archived_at',
        ];

        if ($sortColumn === 'famille') {
            $query->leftJoin('lier', 'utilisateur.idUtilisateur', '=', 'lier.idUtilisateur')
                  ->leftJoin('famille', 'lier.idFamille', '=', 'famille.idFamille')
                  ->select($baseSelect)
                  ->groupBy('utilisateur.idUtilisateur', 'utilisateur.prenom', 'utilisateur.nom', 'utilisateur.email', 'utilisateur.statutValidation', 'utilisateur.archived_at')
                  ->orderBy('famille.idFamille', $sortDirection);
        } else {
            $query->select(array_map(fn ($column) => str_replace('utilisateur.', '', $column), $baseSelect))
                  ->orderBy($sortColumn, $sortDirection);
        }

        $accounts = $query->with(['familles', 'rolesCustom'])
            ->paginate(5)
            ->withQueryString();

        $roles = Role::select('idRole', 'name')->orderBy('name')->get();

        return view('admin.accounts.index', compact('accounts', 'sortColumn', 'sortDirection', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.accounts.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom' => ['required', 'string', 'max:15'],
            'nom' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'unique:utilisateur,email'],
            'languePref' => ['required', 'string', 'max:17'],
            'mdp' => ['required', 'string', 'min:8'],
            'mdp_confirmation' => ['required', 'string', 'same:mdp'],
            'statutValidation' => ['nullable', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:role,idRole'],
        ], [
            'roles.required' => trans('admin.common.roles_required'),
            'roles.min' => trans('admin.common.roles_required'),
            'mdp_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'mdp_confirmation.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        // Créer le compte dans une transaction pour éviter les conditions de course
        // lors de la recherche et de l'insertion d'un ID disponible
        $shouldValidate = $request->boolean('statutValidation');

        $account = DB::transaction(function () use ($validated, $shouldValidate) {
            // Trouver le premier ID disponible dans la transaction
            $availableId = $this->findAvailableId();

            // Créer le compte avec l'ID disponible
            // Désactiver temporairement l'auto-increment pour permettre l'insertion manuelle de l'ID
            $account = new Utilisateur();
            $account->incrementing = false;
            $account->idUtilisateur = $availableId;
            $account->prenom = $validated['prenom'];
            $account->nom = $validated['nom'];
            $account->email = $validated['email'];
            $account->languePref = $validated['languePref'];
            $account->mdp = Hash::make($validated['mdp']);
            $account->statutValidation = $shouldValidate;
            $account->save();

            return $account;
        });

        // Sync roles with model_type automatically set
        $rolesToSync = [];
        foreach ($validated['roles'] as $roleId) {
            $rolesToSync[$roleId] = ['model_type' => Utilisateur::class];
        }
        $account->rolesCustom()->sync($rolesToSync);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.created'));
    }

    public function show(Utilisateur $account): View
    {
        $account->load(['rolesCustom' => function($query) {
            $query->select('role.idRole', 'role.name');
        }]);
        
        // Charger les documents obligatoires pour les rôles de l'utilisateur
        $userRoleIds = $account->rolesCustom()->pluck('avoir.idRole')->toArray();
        
        if (empty($userRoleIds)) {
            $documentsObligatoiresAvecEtat = collect([]);
        } else {
            $documentsObligatoires = DocumentObligatoire::whereHas('roles', function($query) use ($userRoleIds) {
                $query->whereIn('attribuer.idRole', $userRoleIds);
            })->get();
            
            // Pour chaque document obligatoire, trouver les documents uploadés par l'utilisateur
            $documentsObligatoiresAvecEtat = $documentsObligatoires->map(function($docOblig) use ($account) {
                // Récupérer tous les documents de l'utilisateur qui correspondent au nom du document obligatoire
                $documentsUtilisateur = $account->documents()
                    ->where(function ($q) use ($docOblig) {
                        $q->where('nom', 'like', '%' . $docOblig->nom . '%')
                          ->orWhere('nom', $docOblig->nom);
                    })
                    ->orderBy('idDocument', 'desc')
                    ->get();
                
                // Prendre le dernier document (le plus récent)
                $dernierDocument = $documentsUtilisateur->first();
                
                if (!$dernierDocument) {
                    $docOblig->etat = 'non_remis';
                    $docOblig->documentUploaded = null;
                    $docOblig->dateRemise = null;
                } else {
                    // Mapper les états : actif = remis, en_attente = en_cours_validation, valide = valide
                    $etatMapping = [
                        'actif' => 'remis',
                        'en_attente' => 'en_cours_validation',
                        'valide' => 'valide'
                    ];
                    $docOblig->etat = $etatMapping[$dernierDocument->etat] ?? 'remis';
                    $docOblig->documentUploaded = $dernierDocument;
                    
                    // Récupérer la date de remise (date de modification du fichier)
                    if (Storage::disk('public')->exists($dernierDocument->chemin)) {
                        $docOblig->dateRemise = \Carbon\Carbon::createFromTimestamp(
                            Storage::disk('public')->lastModified($dernierDocument->chemin)
                        );
                    } else {
                        $docOblig->dateRemise = null;
                    }
                }
                
                return $docOblig;
            });
        }

        return view('admin.accounts.show', compact('account', 'documentsObligatoiresAvecEtat'));
    }

    public function edit(Utilisateur $account): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfArchived($account)) {
            return $redirect;
        }

        $account->load(['rolesCustom' => function($query) {
            $query->select('role.idRole', 'role.name');
        }]);
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.accounts.edit', compact('account', 'roles'));
    }

    public function update(Request $request, Utilisateur $account): RedirectResponse
    {
        if ($redirect = $this->redirectIfArchived($account)) {
            return $redirect;
        }

        $rules = [
            'prenom' => ['required', 'string', 'max:15'],
            'nom' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'unique:utilisateur,email,' . $account->idUtilisateur . ',idUtilisateur'],
            'languePref' => ['required', 'string', 'max:17'],
            'statutValidation' => ['nullable', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:role,idRole'],
            'mdp' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
        
        $validated = $request->validate($rules, [
            'roles.required' => trans('admin.common.roles_required'),
            'roles.min' => trans('admin.common.roles_required'),
        ]);

        $updateData = [
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'languePref' => $validated['languePref'],
            'statutValidation' => $request->boolean('statutValidation'),
        ];

        if (!empty($validated['mdp'])) {
            $updateData['mdp'] = Hash::make($validated['mdp']);
        }

        $account->update($updateData);

        // Sync roles with model_type automatically set
        $rolesToSync = [];
        foreach ($validated['roles'] as $roleId) {
            $rolesToSync[$roleId] = ['model_type' => Utilisateur::class];
        }
        $account->rolesCustom()->sync($rolesToSync);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.updated'));
    }

    public function archive(Utilisateur $account): RedirectResponse
    {
        if ($account->isArchived()) {
            return redirect()
                ->route('admin.accounts.show', $account)
                ->with('status', trans('admin.accounts_page.messages.already_archived'));
        }

        $account->update([
            'archived_at' => now(),
        ]);

        return redirect()
            ->route('admin.accounts.show', $account)
            ->with('status', trans('admin.accounts_page.messages.archived'));
    }

    private function redirectIfArchived(Utilisateur $account): ?RedirectResponse
    {
        if ($account->isArchived()) {
            return redirect()
                ->route('admin.accounts.show', $account)
                ->with('status', trans('admin.accounts_page.messages.archived_readonly'));
        }

        return null;
    }

    /**
     * Trouve le premier ID disponible dans la table utilisateur
     * Cherche les trous dans la séquence (ex: si 1,2,3,8,9 existent, retourne 4)
     */
    private function findAvailableId(): int
    {
        // Récupérer tous les IDs existants, triés et convertis en tableau pour recherche rapide
        $existingIds = Utilisateur::orderBy('idUtilisateur')
            ->pluck('idUtilisateur')
            ->toArray();

        // Si aucun ID n'existe, commencer à 1
        if (empty($existingIds)) {
            return 1;
        }

        // Convertir en Set pour recherche O(1) au lieu de O(n)
        $existingIdsSet = array_flip($existingIds);
        
        // Trouver le premier ID disponible en partant de 1
        $maxId = max($existingIds);
        
        // Parcourir de 1 jusqu'au maximum pour trouver le premier trou
        for ($id = 1; $id <= $maxId; $id++) {
            if (!isset($existingIdsSet[$id])) {
                return $id;
            }
        }

        // Si tous les IDs jusqu'au maximum sont utilisés, utiliser max + 1
        return $maxId + 1;
    }

    public function validateAccount(Utilisateur $account): RedirectResponse
    {
        if ($redirect = $this->redirectIfArchived($account)) {
            return $redirect;
        }

        $account->update(['statutValidation' => true]);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.validated'));
    }

    public function destroy(Utilisateur $account): RedirectResponse
    {
        if ($redirect = $this->redirectIfArchived($account)) {
            return $redirect;
        }

        $account->delete();

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.deleted'));
    }
    
    /**
     * Valide ou invalide un document obligatoire d'un utilisateur
     */
    public function validateDocument(Request $request, Utilisateur $account, Document $document): RedirectResponse
    {
        // Vérifier que le document appartient à l'utilisateur
        if (!$account->documents()->where('document.idDocument', $document->idDocument)->exists()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'etat' => ['required', 'string', 'in:valide,en_attente'],
        ]);
        
        // Si on valide, passer à 'valide', sinon passer à 'en_attente' (en cours de validation)
        $document->update(['etat' => $validated['etat']]);
        
        return redirect()
            ->route('admin.accounts.show', $account)
            ->with('status', 'document_validated');
    }
    
    /**
     * Supprime un document obligatoire d'un utilisateur
     */
    public function deleteDocument(Request $request, Utilisateur $account, Document $document): RedirectResponse
    {
        // Vérifier que le document appartient à l'utilisateur
        if (!$account->documents()->where('document.idDocument', $document->idDocument)->exists()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Ne pas permettre la suppression si le document est validé
        if ($document->etat === 'valide') {
            return redirect()
                ->route('admin.accounts.show', $account)
                ->with('error', __('auth.document_validated_cannot_delete'));
        }
        
        // Supprimer le fichier physique
        if (Storage::disk('public')->exists($document->chemin)) {
            Storage::disk('public')->delete($document->chemin);
        }
        
        // Détacher le document de l'utilisateur
        $account->documents()->detach($document->idDocument);
        
        // Supprimer le document si aucun autre utilisateur ne l'utilise
        if ($document->utilisateurs()->count() === 0) {
            $document->delete();
        }
        
        return redirect()
            ->route('admin.accounts.show', $account)
            ->with('status', 'document_deleted');
    }
    
    /**
     * Télécharge un document obligatoire d'un utilisateur
     */
    public function downloadDocument(Request $request, Utilisateur $account, Document $document)
    {
        // Vérifier que le document appartient à l'utilisateur
        if (!$account->documents()->where('document.idDocument', $document->idDocument)->exists()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Vérifier que le fichier existe
        if (!Storage::disk('public')->exists($document->chemin)) {
            abort(404, 'File not found.');
        }
        
        // Trouver le document obligatoire correspondant
        // Le nom du document est formaté comme "NomDocumentObligatoire - nom_fichier_original"
        $nomParts = explode(' - ', $document->nom, 2);
        $nomDocumentObligatoire = $nomParts[0];
        
        // Récupérer l'extension du fichier original
        $extension = pathinfo($document->chemin, PATHINFO_EXTENSION);
        if (empty($extension)) {
            // Si pas d'extension dans le chemin, essayer de la récupérer depuis le nom du document
            $extensionParts = explode('.', $document->nom);
            if (count($extensionParts) > 1) {
                $extension = strtolower(end($extensionParts));
            } else {
                $extension = 'pdf'; // Par défaut
            }
        }
        
        // Générer le nom de fichier : Nom_Prenom_NomDocumentObligatoire.extension
        $nomUtilisateur = $account->nom ?? '';
        $prenomUtilisateur = $account->prenom ?? '';
        
        // Nettoyer les noms (remplacer les caractères spéciaux par des underscores)
        $nomUtilisateur = preg_replace('/[^a-zA-Z0-9]/', '_', $nomUtilisateur);
        $prenomUtilisateur = preg_replace('/[^a-zA-Z0-9]/', '_', $prenomUtilisateur);
        $nomDocumentObligatoire = preg_replace('/[^a-zA-Z0-9]/', '_', $nomDocumentObligatoire);
        
        // Construire le nom de fichier
        $fileName = trim($nomUtilisateur . '_' . $prenomUtilisateur . '_' . $nomDocumentObligatoire);
        $fileName = preg_replace('/_+/', '_', $fileName); // Remplacer les underscores multiples par un seul
        $fileName = trim($fileName, '_'); // Enlever les underscores en début/fin
        $fileName .= '.' . $extension;
        
        $filePath = Storage::disk('public')->path($document->chemin);
        
        return Response::download($filePath, $fileName);
    }
}

