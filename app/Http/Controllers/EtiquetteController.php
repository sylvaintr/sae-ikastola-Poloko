<?php
namespace App\Http\Controllers;

use App\Models\Etiquette;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EtiquetteController extends Controller
{
    /**
     *  Methode pour afficher la liste des etiquettes avec possibilité de filtrer par nom et par rôle
     *  les filtres sont appliqués via des requêtes AJAX depuis la vue index des etiquettes, et la méthode data() fournit les données filtrées pour DataTables en mode serveur
     *  la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création ou de la mise à jour d'une étiquette qui utilise cette colonne
     * @param Request|null $request la requête HTTP contenant les paramètres de filtrage (optionnel, si null la méthode utilisera la requête globale)
     * @return View la vue affichant la liste des etiquettes avec les filtres appliqués
     */
    public function index(?Request $request = null): View
    {
        $request = $request ?? request();
        $this->ensureEtiquetteIsPublicColumn();

        $filters = [
            'search' => $request->get('search', ''),
            'role'   => $request->get('role', ''),
        ];

        $query = Etiquette::with('roles');

        if ($filters['search']) {
            $query->where('nom', 'like', '%' . $filters['search'] . '%');
        }

        if ($filters['role']) {
            $roleId = (int) $filters['role'];
            if ($roleId) {
                $query->whereHas('roles', fn($q) => $q->where('role.idRole', $roleId));
            }
        }

        $etiquettes = $query->orderBy('nom')->paginate(10)->appends($request->query());
        $roles      = Role::all();

        return view('etiquettes.index', compact('etiquettes', 'filters', 'roles'));
    }

    /**
     * Methode pour afficher le formulaire de création d'une nouvelle étiquette
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création d'une étiquette qui utilise cette colonne
     * @return View la vue affichant le formulaire de création d'une nouvelle étiquette avec la liste des rôles disponibles
     */
    public function create(): View
    {
        $this->ensureEtiquetteIsPublicColumn();
        $roles = Role::all();
        return view('etiquettes.create', compact('roles'));
    }

    /**
     * Methode pour stocker une nouvelle étiquette dans la base de données
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création d'une étiquette qui utilise cette colonne
     * les rôles associés à l'étiquette sont synchronisés après la création de l'étiquette, en utilisant la méthode sync() de la relation roles() de l'étiquette
     * @param Request $request la requête HTTP contenant les données du formulaire de création d'une nouvelle étiquette, y compris le nom de l'étiquette, sa visibilité publique (optionnelle) et les rôles associés (optionnels)
     * @return RedirectResponse la réponse de redirection vers la liste des étiquettes avec un message de succès ou d'erreur selon le résultat de la création de l'étiquette
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureEtiquetteIsPublicColumn();

        $validated = $request->validate(
            [
                'nom'     => 'required|string|max:50',
                'public'  => ['nullable', 'boolean'],
                'roles'   => ['nullable', 'array'],
                'roles.*' => ['exists:role,idRole'],
            ]
        );

        $payload = ['nom' => $validated['nom']];
        if (\Illuminate\Support\Facades\Schema::hasColumn('etiquette', 'public')) {
            $payload['public'] = (bool) ($validated['public'] ?? false);
        }

        $etiquette   = Etiquette::create($payload);
        $rolesToSync = [];
        $roles       = $validated['roles'] ?? [];
        foreach ($roles as $roleId) {
            $rolesToSync[$roleId] = [];
        }
        $etiquette->roles()->sync($rolesToSync);

        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteCreee'));
    }

    /**
     * Methode pour afficher le formulaire de modification d'une étiquette existante
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la modification d'une étiquette qui utilise cette colonne
     * @param string $idEtiquette l'identifiant de l'étiquette à modifier
     * @return View|RedirectResponse la vue affichant le formulaire de modification de l'étiquette avec les données de l'étiquette pré-remplies et la liste des rôles disponibles, ou une réponse de redirection vers la liste des étiquettes avec un message d'erreur si l'étiquette n'existe pas
     */
    public function edit(string $idEtiquette): View | RedirectResponse
    {
        $this->ensureEtiquetteIsPublicColumn();
        try {
            $etiquette = Etiquette::findOrFail($idEtiquette);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.etiquettes.index')->with('error', __('etiquette.errorEtiquetteNonTrouvee'));
        }
        $etiquette->load('roles');
        $roles = Role::all();
        return view('etiquettes.edit', compact('etiquette', 'roles'));
    }

    /**
     * methode pour mettre a jour une etiquette
     * @param Request $request parameters du formulaire (nom de l'étiquette, visibilité publique, rôles associés)
     * @param Etiquette $etiquette etiquette a mettre a jour
     * @return RedirectResponse redirection vers la liste des etiquettes avec un message de succes ou d'erreur
     */
    public function update(Request $request, Etiquette $etiquette): RedirectResponse
    {
        $this->ensureEtiquetteIsPublicColumn();

        $validated = $request->validate([
            'nom'     => 'required|string|max:50',
            'public'  => ['nullable', 'boolean'],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['exists:role,idRole'],
        ]);

        $rolesToSync = [];
        $roles       = $validated['roles'] ?? [];
        foreach ($roles as $roleId) {
            $rolesToSync[$roleId] = [];
        }
        $etiquette->roles()->sync($rolesToSync);

        $updatePayload = [
            'nom' => $validated['nom'],
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('etiquette', 'public')) {
            $updatePayload['public'] = (bool) ($validated['public'] ?? false);
        }

        $etiquette->update($updatePayload);
        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteMiseAJour'));
    }

    /**
     * Methode pour supprimer une étiquette de la base de données
     * @param Etiquette $etiquette l'étiquette à supprimer
     * @return RedirectResponse la réponse de redirection vers la liste des étiquettes avec un message de succès ou d'erreur selon le résultat de la suppression de l'étiquette
     */
    public function destroy(Etiquette $etiquette): RedirectResponse
    {
        $etiquette->delete();
        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteSupprimee'));
    }

    /**
     * Methode pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà
     * @return void
     */
    private function ensureEtiquetteIsPublicColumn(): void
    {
        if (! Schema::hasColumn('etiquette', 'public')) {
            Schema::table('etiquette', function ($table) {
                $table->boolean('public')->default(false)->after('nom');
            });
        }
    }

    /**
     * Methode pour fournir les données des étiquettes filtrées pour DataTables en mode serveur
     * les filtres sont appliqués via des requêtes AJAX depuis la vue index des etiquettes, et la méthode data() fournit les données filtrées pour DataTables en mode serveur
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création ou de la mise à jour d'une étiquette qui utilise cette colonne
     * @param Request|null $request la requête HTTP contenant les paramètres de filtrage (optionnel, si null la méthode utilisera la requête globale)
     * @return Response|JsonResponse la réponse HTTP contenant les données des étiquettes filtrées au format JSON pour DataTables en mode serveur ou une réponse de redirection vers la liste des étiquettes avec un message d'erreur si une erreur survient lors de la récupération des données
     */
    public function data(Request $request = null): Response | JsonResponse
    {
        // accept filters from DataTable (name, role)
        $request = $request ?? request();
        $query   = Etiquette::with('roles');
        if ($request->filled('name')) {
            $like = "%{$request->input('name')}%";
            $query->where('nom', 'like', $like);
        }
        if ($request->filled('role')) {
            $roleId = (int) $request->input('role');
            if ($roleId) {
                $query->whereHas('roles', function ($q) use ($roleId) {
                    $this->applyRoleWhereHas($q, $roleId);
                });
            }
        }

        return DataTables::of($query)
            ->addColumn('idEtiquette', function ($etiquette) {
                return $this->columnIdEtiquette($etiquette);
            })
            ->addColumn('nom', function ($etiquette) {
                return $this->columnNom($etiquette);
            })
            ->addColumn('roles', function ($etiquette) {
                return $this->columnRolesText($etiquette);
            })
            ->addColumn('actions', function ($etiquette) {
                return $this->columnActionsHtml($etiquette);
            })
        // Allow searching on the virtual 'roles' column by relation
            ->filterColumn('roles', [$this, 'filterColumnRolesCallback'])
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Methode pour appliquer le filtre de rôle dans la requête de DataTables en mode serveur
     * utilisée par la méthode data() pour filtrer les étiquettes par rôle lorsque le filtre de rôle est appliqué depuis la vue index des étiquettes
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création ou de la mise à jour d'une étiquette qui utilise cette colonne
     * @param \Illuminate\Database\Eloquent\Builder $q la requête Eloquent sur laquelle appliquer le filtre de rôle
     * @param int $roleId l'identifiant du rôle à filtrer, obtenu à partir du paramètre de filtrage de rôle envoyé depuis la vue index des étiquettes
     * @return void
     */
    public function applyRoleWhereHas($q, int $roleId): void
    {
        $table = $q->getModel()->getTable();
        $q->where($table . '.idRole', $roleId);
    }

    /**
     * Methode pour filtrer la colonne 'roles' de DataTables en mode serveur en fonction d'un mot-clé de recherche
     * utilisée par la méthode data() pour permettre la recherche sur la colonne 'roles' de DataTables en mode serveur, en filtrant les étiquettes par les rôles associés dont le nom ou le display_name correspond au mot-clé de recherche
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création ou de la mise à jour d'une étiquette qui utilise cette colonne
     * @param \Illuminate\Database\Eloquent\Builder $query la requête Eloquent sur laquelle appliquer le filtre de recherche sur la colonne 'roles'
     * @param string $keyword le mot-clé de recherche saisi dans le champ de recherche de la colonne 'roles' de DataTables, utilisé pour filtrer les étiquettes par les rôles associés dont le nom ou le display_name correspond à ce mot-clé
     * @return void
     */
    public function filterRolesColumnByKeyword($query, string $keyword)
    {
        $like = "%{$keyword}%";
        $query->whereHas('roles', function ($q) use ($like) {
            $q->where('name', 'like', $like)->orWhere('display_name', 'like', $like);
        });
    }

    /**
     * Methode pour obtenir la valeur de la colonne 'idEtiquette' pour une étiquette donnée, utilisée par la méthode data() pour fournir les données de la colonne 'idEtiquette' de DataTables en mode serveur
     * @param Etiquette $etiquette l'étiquette pour laquelle obtenir la valeur de la colonne 'idEtiquette'
     * @return int la valeur de la colonne 'idEtiquette' pour l'étiquette donnée
     */
    public function columnIdEtiquette(Etiquette $etiquette): int
    {
        return $etiquette->idEtiquette;
    }

    /**
     * Methode pour obtenir la valeur de la colonne 'nom' pour une étiquette donnée, utilisée par la méthode data() pour fournir les données de la colonne 'nom' de DataTables en mode serveur
     * @param Etiquette $etiquette l'étiquette pour laquelle obtenir la valeur de la colonne 'nom'
     * @return string la valeur de la colonne 'nom' pour l'étiquette donnée
     */
    public function columnNom(Etiquette $etiquette): string
    {
        return $etiquette->nom;
    }

    /**
     * Methode pour obtenir la valeur de la colonne 'roles' au format texte pour une étiquette donnée, utilisée par la méthode data() pour fournir les données de la colonne 'roles' de DataTables en mode serveur
     * la valeur de la colonne 'roles' est obtenue en récupérant les rôles associés à l'étiquette via la relation roles(), puis en extrayant le nom de chaque rôle et en les joignant avec une virgule pour former une chaîne de caractères représentant les rôles associés à l'étiquette
     * @param Etiquette $etiquette l'étiquette pour laquelle obtenir la valeur de la colonne 'roles'
     * @return string une chaîne de caractères représentant les rôles associés à l'étiquette donnée, obtenue en joignant les noms des rôles associés avec une virgule
     */
    public function columnRolesText(Etiquette $etiquette): string
    {
        return $etiquette->roles->pluck('name')->join(', ');
    }

    /**
     * Methode pour obtenir le code HTML de la colonne d'actions pour une étiquette donnée, utilisée par la méthode data() pour fournir les données de la colonne 'actions' de DataTables en mode serveur
     * le code HTML de la colonne d'actions est généré en rendant une vue Blade spécifique (etiquettes.template.colonne-action) qui contient les boutons d'action (modifier, supprimer) pour l'étiquette donnée, en passant l'étiquette à la vue pour qu'elle puisse générer les liens d'action appropriés
     * @param Etiquette $etiquette l'étiquette pour laquelle obtenir le code HTML de la colonne d'actions
     * @return View le code HTML de la colonne d'actions pour l'étiquette donnée, généré en rendant la vue Blade 'etiquettes.template.colonne-action' avec l'étiquette passée en paramètre
     */
    public function columnActionsHtml(Etiquette $etiquette): View
    {
        return view('etiquettes.template.colonne-action', compact('etiquette'));
    }

    /**
     * Methode pour filtrer la colonne 'roles' de DataTables en mode serveur en fonction d'un mot-clé de recherche
     * utilisée par la méthode data() pour permettre la recherche sur la colonne 'roles' de DataTables en mode serveur, en filtrant les étiquettes par les rôles associés dont le nom ou le display_name correspond au mot-clé de recherche
     * la méthode ensureEtiquetteIsPublicColumn() est appelée pour ajouter la colonne 'public' à la table 'etiquette' si elle n'existe pas déjà, afin de éviter les erreurs lors de la création ou de la mise à jour d'une étiquette qui utilise cette colonne
     * @param \Illuminate\Database\Eloquent\Builder $query la requête Eloquent sur laquelle appliquer le filtre de recherche sur la colonne 'roles'
     * @param string $keyword le mot-clé de recherche saisi dans le champ de recherche de la colonne 'roles' de DataTables, utilisé pour filtrer les étiquettes par les rôles associés dont le nom ou le display_name correspond à ce mot-clé
     * @return void
     */
    public function filterColumnRolesCallback($query, string $keyword)
    {
        $this->filterRolesColumnByKeyword($query, $keyword);
    }
}
