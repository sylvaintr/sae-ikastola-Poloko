<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesDocumentDownloads;
use App\Models\Document;
use App\Models\DocumentObligatoire;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccountController extends Controller
{
    use HandlesDocumentDownloads;
    /**
     * Méthode pour afficher la liste des comptes utilisateurs. Cette méthode gère la recherche, le filtrage par rôle, et le tri des comptes. Elle récupère les comptes utilisateurs en fonction des critères de recherche et de filtrage fournis dans la requête, puis les pagine pour l'affichage. Les rôles disponibles sont également récupérés pour permettre le filtrage par rôle dans la vue. Enfin, la méthode retourne la vue "admin.accounts.index" avec les données nécessaires pour afficher la liste des comptes utilisateurs.
     * @param Request $request La requête HTTP contenant les paramètres de recherche, de filtrage et de tri pour les comptes utilisateurs
     * @return View La vue avec les comptes utilisateurs paginés, les rôles disponibles, et les informations de tri pour l'affichage
     */
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
        $sortColumn    = $request->get('sort', 'nom');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortColumns = ['prenom', 'nom', 'email', 'statutValidation', 'idUtilisateur', 'famille'];
        if (! in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'nom';
        }

        if (! in_array($sortDirection, ['asc', 'desc'])) {
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
            $query->select(array_map(fn($column) => str_replace('utilisateur.', '', $column), $baseSelect))
                ->orderBy($sortColumn, $sortDirection);
        }

        $accounts = $query->with(['familles', 'rolesCustom'])
            ->paginate(5)
            ->withQueryString();

        $roles = Role::select('idRole', 'name')->orderBy('name')->get();

        return view('admin.accounts.index', compact('accounts', 'sortColumn', 'sortDirection', 'roles'));
    }

    /**
     * Méthode pour afficher le formulaire de création d'un compte utilisateur. Cette méthode récupère les rôles disponibles pour permettre à l'administrateur de sélectionner les rôles à attribuer au nouveau compte, puis retourne la vue "admin.accounts.create" avec les données nécessaires pour afficher le formulaire de création de compte utilisateur.
     * @return View La vue du formulaire de création de compte utilisateur avec les rôles disponibles pour l'attribution
     */
    public function create(): View
    {
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.accounts.create', compact('roles'));
    }

    /**
     * Méthode pour gérer la soumission du formulaire de création d'un compte utilisateur. Cette méthode valide les données soumises par l'administrateur, crée un nouveau compte utilisateur dans la base de données avec les informations fournies, assigne les rôles sélectionnés au compte, puis redirige vers la liste des comptes utilisateurs avec un message de succès. Si la validation échoue, l'administrateur est redirigé en arrière avec des erreurs de validation appropriées. Cette méthode est essentielle pour permettre à l'administrateur de créer de nouveaux comptes utilisateurs et de leur attribuer des rôles spécifiques.
     * @param Request $request La requête HTTP contenant les données du formulaire de création de compte utilisateur
     * @return RedirectResponse Redirection vers la liste des comptes utilisateurs avec un message de succès ou redirection en arrière avec des erreurs de validation
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom'           => ['required', 'string', 'max:15'],
            'nom'              => ['required', 'string', 'max:15'],
            'email'            => ['required', 'email', 'unique:utilisateur,email'],
            'languePref'       => ['required', 'string', 'max:17'],
            'mdp'              => ['required', 'string', 'min:8'],
            'mdp_confirmation' => ['required', 'string', 'same:mdp'],
            'dateNaissance'    => ['nullable', 'date', 'before:today'],
            'statutValidation' => ['nullable', 'boolean'],
            'roles'            => ['required', 'array', 'min:1'],
            'roles.*'          => ['exists:role,idRole'],
        ], [
            'roles.required'            => trans('admin.common.roles_required'),
            'roles.min'                 => trans('admin.common.roles_required'),
            'mdp_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'mdp_confirmation.same'     => 'Les mots de passe ne correspondent pas.',
        ]);

        // Créer le compte dans une transaction pour éviter les conditions de course
        // lors de la recherche et de l'insertion d'un ID disponible
        $shouldValidate = $request->boolean('statutValidation');

        $account = DB::transaction(function () use ($validated, $shouldValidate) {
            // Trouver le premier ID disponible dans la transaction
            $availableId = $this->findAvailableId();

            // Créer le compte avec l'ID disponible
            // Désactiver temporairement l'auto-increment pour permettre l'insertion manuelle de l'ID
            $account                   = new Utilisateur();
            $account->incrementing     = false;
            $account->idUtilisateur    = $availableId;
            $account->prenom           = $validated['prenom'];
            $account->nom              = $validated['nom'];
            $account->email            = $validated['email'];
            $account->languePref       = $validated['languePref'];
            $account->mdp              = Hash::make($validated['mdp']);
            $account->dateNaissance    = $validated['dateNaissance'] ?? null;
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

    /**
     * Méthode pour afficher les détails d'un compte utilisateur. Cette méthode charge les rôles associés au compte utilisateur ainsi que les documents obligatoires liés aux rôles de l'utilisateur. Pour chaque document obligatoire, elle vérifie si l'utilisateur a soumis un document correspondant et détermine l'état de chaque document (non remis, remis, en cours de validation, ou valide). Enfin, la méthode retourne la vue "admin.accounts.show" avec les données du compte utilisateur et les documents obligatoires avec leur état pour l'affichage des détails du compte utilisateur.
     * @param Utilisateur $account Le compte utilisateur dont les détails doivent être affichés
     * @return View La vue avec les données du compte utilisateur et les documents obligatoires avec leur état pour l'affichage des détails du compte utilisateur
     */
    public function show(Utilisateur $account): View
    {
        $account->load(['rolesCustom' => function ($query) {
            $query->select('role.idRole', 'role.name');
        }]);

        // Charger les documents obligatoires pour les rôles de l'utilisateur
        $userRoleIds = $account->rolesCustom()->pluck('avoir.idRole')->toArray();

        if (empty($userRoleIds)) {
            $documentsObligatoiresAvecEtat = collect([]);
        } else {
            $documentsObligatoires = DocumentObligatoire::whereHas('roles', function ($query) use ($userRoleIds) {
                $query->whereIn('attribuer.idRole', $userRoleIds);
            })->get();

            // Pour chaque document obligatoire, trouver les documents uploadés par l'utilisateur
            $documentsObligatoiresAvecEtat = $documentsObligatoires->map(function ($docOblig) use ($account) {
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

                if (! $dernierDocument) {
                    $docOblig->etat             = 'non_remis';
                    $docOblig->documentUploaded = null;
                    $docOblig->dateRemise       = null;
                } else {
                    // Mapper les états : actif = remis, en_attente = en_cours_validation, valide = valide
                    $etatMapping = [
                        'actif'      => 'remis',
                        'en_attente' => 'en_cours_validation',
                        'valide'     => 'valide',
                    ];
                    $docOblig->etat             = $etatMapping[$dernierDocument->etat] ?? 'remis';
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

    /**
     * Méthode pour afficher le formulaire d'édition d'un compte utilisateur. Cette méthode vérifie si le compte est archivé et redirige en conséquence, puis charge les rôles associés au compte et les rôles disponibles pour l'affichage dans le formulaire. Enfin, elle retourne la vue "admin.accounts.edit" avec les données nécessaires pour afficher le formulaire d'édition du compte utilisateur.
     * @param Utilisateur $account Le compte utilisateur à éditer
     * @return View|RedirectResponse La vue du formulaire d'édition du compte utilisateur avec les rôles associés et disponibles, ou une redirection si le compte est archivé
     */
    public function edit(Utilisateur $account): View | RedirectResponse
    {
        if ($redirect = $this->redirectIfArchived($account)) {
            return $redirect;
        }

        $account->load(['rolesCustom' => function ($query) {
            $query->select('role.idRole', 'role.name');
        }]);
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.accounts.edit', compact('account', 'roles'));
    }

    /**
     * Méthode pour gérer la soumission du formulaire d'édition d'un compte utilisateur. Cette méthode valide les données soumises par l'administrateur, met à jour les informations du compte utilisateur dans la base de données, synchronise les rôles attribués au compte, puis redirige vers la liste des comptes utilisateurs avec un message de succès. Si la validation échoue ou si le compte est archivé, l'administrateur est redirigé en conséquence avec des erreurs de validation ou un message indiquant que le compte est archivé. Cette méthode est essentielle pour permettre à l'administrateur de modifier les informations des comptes utilisateurs et de gérer leurs rôles.
     * @param Request $request La requête HTTP contenant les données du formulaire d'édition du compte utilisateur
     * @param Utilisateur $account Le compte utilisateur à mettre à jour
     * @return RedirectResponse Redirection vers la liste des comptes utilisateurs avec un message de succès ou redirection en arrière avec des erreurs de validation ou un message d'archivage
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue
     */
    public function update(Request $request, Utilisateur $account): RedirectResponse
    {
        if ($redirect = $this->redirectIfArchived($account)) {
            return $redirect;
        }

        $rules = [
            'prenom'           => ['required', 'string', 'max:15'],
            'nom'              => ['required', 'string', 'max:15'],
            'email'            => ['required', 'email', 'unique:utilisateur,email,' . $account->idUtilisateur . ',idUtilisateur'],
            'languePref'       => ['required', 'string', 'max:17'],
            'statutValidation' => ['nullable', 'boolean'],
            'roles'            => ['required', 'array', 'min:1'],
            'roles.*'          => ['exists:role,idRole'],
            'mdp'              => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        $validated = $request->validate($rules, [
            'roles.required' => trans('admin.common.roles_required'),
            'roles.min'      => trans('admin.common.roles_required'),
        ]);

        $updateData = [
            'prenom'           => $validated['prenom'],
            'nom'              => $validated['nom'],
            'email'            => $validated['email'],
            'languePref'       => $validated['languePref'],
            'statutValidation' => $request->boolean('statutValidation'),
        ];

        if (! empty($validated['mdp'])) {
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

    /**
     * Méthode pour archiver un compte utilisateur. Cette méthode vérifie si le compte est déjà archivé et redirige en conséquence, puis met à jour le champ "archived_at" du compte avec la date et l'heure actuelles pour marquer le compte comme archivé. Enfin, elle redirige vers les détails du compte utilisateur avec un message indiquant que le compte a été archivé. Cette méthode est utilisée pour marquer un compte utilisateur comme inactif sans le supprimer de la base de données, permettant ainsi de conserver les données associées au compte tout en empêchant son utilisation.
     * @param Utilisateur $account Le compte utilisateur à archiver
     * @return RedirectResponse Redirection vers les détails du compte utilisateur avec un message d'archivage ou redirection en arrière avec un message indiquant que le compte est déjà archivé
     */
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

    /**
     * Méthode pour vérifier si un compte utilisateur est archivé et rediriger en conséquence. Si le compte est archivé, l'utilisateur est redirigé vers les détails du compte avec un message indiquant que le compte est en lecture seule en raison de son statut d'archivage. Si le compte n'est pas archivé, la méthode retourne null pour permettre la poursuite normale du processus. Cette méthode est utilisée pour empêcher les modifications sur les comptes archivés tout en permettant aux administrateurs de consulter les détails des comptes archivés.
     * @param Utilisateur $account Le compte utilisateur à vérifier pour l'archivage
     * @return RedirectResponse|null Redirection vers les détails du compte utilisateur avec un message d'archivage si le compte est archivé, ou null si le compte n'est pas archivé pour permettre la poursuite normale du processus
     */
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
     * Méthode pour trouvé le premier ID disponible dans la table utilisateur Cherche les trous dans la séquence (ex: si 1,2,3,8,9 existent, retourne 4)
     * @return int Le premier ID disponible pour un nouveau compte utilisateur
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
            if (! isset($existingIdsSet[$id])) {
                return $id;
            }
        }

        // Si tous les IDs jusqu'au maximum sont utilisés, utiliser max + 1
        return $maxId + 1;
    }

    /**
     * Méthode pour valider un compte utilisateur. Cette méthode vérifie si le compte est archivé et redirige en conséquence, puis met à jour le champ "statutValidation" du compte pour le marquer comme validé. Enfin, elle redirige vers la liste des comptes utilisateurs avec un message indiquant que le compte a été validé. Cette méthode est utilisée pour marquer un compte utilisateur comme validé, ce qui peut être nécessaire pour permettre à l'utilisateur d'accéder à certaines fonctionnalités ou ressources de l'application.
     * @param Utilisateur $account Le compte utilisateur à valider
     * @return RedirectResponse Redirection vers la liste des comptes utilisateurs avec un message de validation ou redirection en arrière avec un message indiquant que le compte est archivé
     */
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

    /**
     * Méthode pour supprimer un compte utilisateur. Cette méthode vérifie si le compte est archivé et redirige en conséquence, puis supprime le compte de la base de données. Enfin, elle redirige vers la liste des comptes utilisateurs avec un message indiquant que le compte a été supprimé. Cette méthode est utilisée pour supprimer définitivement un compte utilisateur de l'application, ce qui peut être nécessaire pour des raisons de gestion des utilisateurs ou de conformité aux politiques de confidentialité.
     * @param Utilisateur $account Le compte utilisateur à supprimer
     * @return RedirectResponse Redirection vers la liste des comptes utilisateurs avec un message de suppression ou redirection en arrière avec un message indiquant que le compte est archivé
     */
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
     * Méthode pour valider ou invalider un document obligatoire d'un utilisateur
     * @param Request $request La requête HTTP contenant les données de validation du document
     * @param Utilisateur $account Le compte utilisateur auquel le document appartient
     * @param Document $document Le document à valider ou invalider
     * @return RedirectResponse Redirection vers les détails du compte utilisateur avec un message de validation ou d'invalidation du document, ou redirection en arrière avec un message indiquant que le document n'appartient pas à l'utilisateur ou que l'action n'est pas autorisée
     */
    public function validateDocument(Request $request, Utilisateur $account, Document $document): RedirectResponse
    {
        // Vérifier que le document appartient à l'utilisateur
        if (! $account->documents()->where('document.idDocument', $document->idDocument)->exists()) {
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
     * Méthode pour supprimer un document obligatoire d'un utilisateur. Cette méthode vérifie que le document appartient à l'utilisateur et que le document n'est pas validé avant de procéder à la suppression. Si le document est validé, la suppression est interdite et l'utilisateur est redirigé avec un message d'erreur. Si le document peut être supprimé, le fichier physique est supprimé du stockage, le lien entre le document et l'utilisateur est détaché, et si aucun autre utilisateur n'utilise ce document, il est supprimé de la base de données. Enfin, l'utilisateur est redirigé vers les détails du compte avec un message indiquant que le document a été supprimé.
     * @param Utilisateur $account Le compte utilisateur auquel le document appartient
     * @param Document $document Le document à supprimer
     * @return RedirectResponse Redirection vers les détails du compte utilisateur avec un message de suppression du document ou redirection en arrière avec un message d'erreur si le document n'appartient pas à l'utilisateur ou si le document est validé
     */
    public function deleteDocument(Utilisateur $account, Document $document): RedirectResponse
    {
        // Vérifier que le document appartient à l'utilisateur
        if (! $account->documents()->where('document.idDocument', $document->idDocument)->exists()) {
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
     * Méthode pour télécharger un document obligatoire d'un utilisateur. Cette méthode vérifie que le document appartient à l'utilisateur, puis utilise la méthode "downloadDocumentWithFormattedName" du trait "HandlesDocumentDownloads" pour gérer le téléchargement du fichier avec un nom de fichier formaté. Si le document n'appartient pas à l'utilisateur, une erreur 403 est retournée. Cette méthode est utilisée pour permettre aux administrateurs de télécharger les documents soumis par les utilisateurs tout en assurant que seuls les documents appartenant à l'utilisateur peuvent être téléchargés.
     * @param Utilisateur $account Le compte utilisateur auquel le document appartient
     * @param Document $document Le document à télécharger
     * @return Response|RedirectResponse La réponse de téléchargement du fichier ou redirection en arrière avec un message d'erreur si le document n'appartient pas à l'utilisateur
     */
    public function downloadDocument(Utilisateur $account, Document $document)
    {
        return $this->downloadDocumentWithFormattedName($account, $document);
    }
}
