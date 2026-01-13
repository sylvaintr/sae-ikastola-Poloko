<?php

namespace App\Http\Controllers;

use App\Models\Etiquette;
use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Schema;


class EtiquetteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(?Request $request = null)
    {
        $request = $request ?? request();
        $this->ensureEtiquetteIsPublicColumn();

        $filters = [
            'search' => $request->get('search', ''),
            'role' => $request->get('role', ''),
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
        $roles = Role::all();

        return view('etiquettes.index', compact('etiquettes', 'filters', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureEtiquetteIsPublicColumn();
        $roles = Role::all();
        return view('etiquettes.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->ensureEtiquetteIsPublicColumn();

        $validated = $request->validate(
            [
                'nom' => 'required|string|max:50',
                'is_public' => ['nullable', 'boolean'],
                'roles' => ['nullable', 'array'],
                'roles.*' => ['exists:role,idRole'],
            ]
        );

        $payload = ['nom' => $validated['nom']];
        if (\Illuminate\Support\Facades\Schema::hasColumn('etiquette', 'is_public')) {
            $payload['is_public'] = (bool) ($validated['is_public'] ?? false);
        }

        $etiquette = Etiquette::create($payload);
        $rolesToSync = [];
        $roles = $validated['roles'] ?? [];
        foreach ($roles as $roleId) {
            $rolesToSync[$roleId] = [];
        }
        $etiquette->roles()->sync($rolesToSync);


        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteCreee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $idEtiquette)
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
     * @param Request $request parameters du formulaire
     * @param Etiquette $etiquette etiquette a mettre a jour
     * @return \Illuminate\Http\RedirectResponse redirection vers la liste des etiquettes avec un message de succes ou d'erreur
     */
    public function update(Request $request, Etiquette $etiquette)
    {
        $this->ensureEtiquetteIsPublicColumn();

        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'is_public' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:role,idRole'],
        ]);

        $rolesToSync = [];
        $roles = $validated['roles'] ?? [];
        foreach ($roles as $roleId) {
            $rolesToSync[$roleId] = [];
        }
        $etiquette->roles()->sync($rolesToSync);

        $updatePayload = [
            'nom' => $validated['nom'],
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('etiquette', 'is_public')) {
            $updatePayload['is_public'] = (bool) ($validated['is_public'] ?? false);
        }

        $etiquette->update($updatePayload);
        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteMiseAJour'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Etiquette $etiquette)
    {
        $etiquette->delete();
        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteSupprimee'));
    }

    /**
     * Ajoute la colonne is_public sur etiquette si absente (pas de nouvelle migration).
     */
    private function ensureEtiquetteIsPublicColumn(): void
    {
        if (!Schema::hasColumn('etiquette', 'is_public')) {
            Schema::table('etiquette', function ($table) {
                $table->boolean('is_public')->default(false)->after('nom');
            });
        }
    }

    /**
     * Fournit les données pour DataTables en mode serveur.
     */
    public function data(Request $request = null)
    {
        // accept filters from DataTable (name, role)
        $request = $request ?? request();
        $query = Etiquette::with('roles');
        if ($request->filled('name')) {
            $like = "%{$request->input('name')}%";
            $query->where('nom', 'like', $like);
        }
        if ($request->filled('role')) {
            $roleId = (int)$request->input('role');
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
     * Apply a where condition on the related roles query by idRole.
     * Made public so we can unit test the logic used inside closures.
     */
    public function applyRoleWhereHas($q, int $roleId)
    {
        $table = $q->getModel()->getTable();
        $q->where($table . '.idRole', $roleId);
    }

    /**
     * Extracted logic for filtering the virtual 'roles' column by keyword.
     */
    public function filterRolesColumnByKeyword($query, string $keyword)
    {
        $like = "%{$keyword}%";
        $query->whereHas('roles', function ($q) use ($like) {
            $q->where('name', 'like', $like)->orWhere('display_name', 'like', $like);
        });
    }

    /**
     * Column extractors — separated from closures so unit tests can call them directly.
     */
    public function columnIdEtiquette($etiquette)
    {
        return $etiquette->idEtiquette;
    }

    public function columnNom($etiquette)
    {
        return $etiquette->nom;
    }

    public function columnRolesText($etiquette)
    {
        return $etiquette->roles->pluck('name')->join(', ');
    }

    public function columnActionsHtml($etiquette)
    {
        return view('etiquettes.template.colonne-action', compact('etiquette'));
    }

    /** Callable used by DataTables filter registration so it can be unit-tested. */
    public function filterColumnRolesCallback($query, string $keyword)
    {
        $this->filterRolesColumnByKeyword($query, $keyword);
    }
}
