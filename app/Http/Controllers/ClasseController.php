<?php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ClasseController extends Controller
{
    /**
     * Méthode d'affichage de la liste des classes avec filtres de recherche et pagination.
     * @param Request $request Requête HTTP contenant les paramètres de filtre
     * @return View Vue de la liste des classes avec les données filtrées et paginées
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search', ''),
            'niveau' => $request->get('niveau', ''),
        ];

        $query = Classe::query();

        if ($filters['search']) {
            $query->where('nom', 'like', '%' . $filters['search'] . '%');
        }

        if ($filters['niveau']) {
            $query->where('niveau', $filters['niveau']);
        }

        $classes = $query->orderBy('nom')->paginate(10)->appends($request->query());
        $levels  = Classe::select('niveau')->distinct()->orderBy('niveau')->pluck('niveau');

        return view('admin.classes.index', compact('classes', 'filters', 'levels'));
    }

    /**
     * Méthode pour fournir les données des classes au format JSON pour DataTables.
     * @return \Yajra\DataTables\DataTableAbstract Données des classes formatées pour DataTables
     */
    public function data()
    {
        $query = Classe::query();

        return DataTables::of($query)
            ->addColumn('actions', function ($classe) {
                return view('admin.classes.partials.actions', compact('classe'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Méthode pour afficher la fiche d'une classe avec la liste de ses élèves.
     * @param Classe $classe Classe à afficher
     * @return View Vue de la fiche de la classe avec la liste des élèves
     */
    public function show(Classe $classe): View
    {
        $classe->load(['enfants' => function ($q) {
            $q->orderBy('prenom')->orderBy('nom');
        }]);

        return view('admin.classes.show', compact('classe'));
    }

    /**
     * Méthode pour afficher le formulaire de création d'une nouvelle classe avec la liste des enfants disponibles et les niveaux existants.
     * @return View Vue du formulaire de création de classe avec les données nécessaires
     */
    public function create(): View
    {
        $children = Enfant::whereNull('idClasse')
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        $levels = Classe::select('niveau')
            ->distinct()
            ->orderBy('niveau')
            ->pluck('niveau');

        return view('admin.classes.create', compact('children', 'levels'));
    }

    /**
     * Méthode pour stocker une nouvelle classe dans la base de données et associer les enfants sélectionnés.
     * @param Request $request Requête HTTP contenant les données de la nouvelle classe et les enfants associés
     * @return RedirectResponse Redirection vers la liste des classes avec un message de succès ou d'erreur
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom'        => 'required|string|max:255',
            'niveau'     => 'required|string|max:50',
            'children'   => 'required|array',
            'children.*' => [
                'required',
                Rule::exists('enfant', 'idEnfant')->whereNull('idClasse'),
            ],
        ]);

        $classe = Classe::create($request->only('nom', 'niveau'));

        Enfant::whereIn('idEnfant', $request->children)
            ->update(['idClasse' => $classe->idClasse]);

        return redirect()
            ->route('admin.classes.index')
            ->with('success', __('classes.created_success'));
    }

    /**
     * Méthode pour afficher le formulaire d'édition d'une classe existante avec la liste des enfants disponibles, les niveaux existants et les enfants déjà associés.
     * @param Classe $classe Classe à éditer
     * @return View Vue du formulaire d'édition de classe avec les données nécessaires
     */
    public function edit(Classe $classe): View
    {
        $children = Enfant::whereNull('idClasse')
            ->orWhere('idClasse', $classe->idClasse)
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        $levels = Classe::select('niveau')
            ->distinct()
            ->orderBy('niveau')
            ->pluck('niveau');

        $selectedChildrenIds = $classe->enfants()->pluck('idEnfant')->toArray();

        return view('admin.classes.edit', compact('classe', 'children', 'levels', 'selectedChildrenIds'));
    }

    /**
     * Méthode pour mettre à jour une classe existante dans la base de données et synchroniser les enfants associés.
     * @param Request $request Requête HTTP contenant les données mises à jour de la classe et les enfants associés
     * @param Classe $classe Classe à mettre à jour
     * @return RedirectResponse Redirection vers la liste des classes avec un message de succès ou d'erreur
     */
    public function update(Request $request, Classe $classe): RedirectResponse
    {
        $request->validate([
            'nom'        => 'required|string|max:255',
            'niveau'     => 'required|string|max:50',
            'children'   => 'required|array',
            'children.*' => [
                'required',
                Rule::exists('enfant', 'idEnfant')->where(function ($query) use ($classe) {
                    $query->whereNull('idClasse')
                        ->orWhere('idClasse', $classe->idClasse);
                }),
            ],
        ]);

        DB::transaction(function () use ($request, $classe) {
            $classe->update($request->only('nom', 'niveau'));

            $selectedIds = $request->input('children', []);

            $currentIds = $classe->enfants()->pluck('idEnfant')->toArray();

            $toDetach = array_diff($currentIds, $selectedIds);

            $toAttach = array_diff($selectedIds, $currentIds);

            if (! empty($toDetach)) {
                Enfant::whereIn('idEnfant', $toDetach)->update(['idClasse' => null]);
            }

            if (! empty($toAttach)) {
                Enfant::whereIn('idEnfant', $toAttach)->update(['idClasse' => $classe->idClasse]);
            }
        });

        return redirect()
            ->route('admin.classes.index')
            ->with('success', __('classes.updated_success'));
    }

    /**
     * Supprime une classe et détache tous les enfants associés.
     *
     * @param Classe $classe Classe à supprimer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Classe $classe)
    {
        $classe->enfants()->update(['idClasse' => null]);

        $classe->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', __('classes.deleted_success'));
    }
}
