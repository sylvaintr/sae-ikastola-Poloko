<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Enfant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ClasseController extends Controller
{
    /**
     * Affiche la page listant toutes les classes.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.classes.index');
    }

    /**
     * Retourne les données des classes au format DataTables (AJAX).
     *
     * @return \Yajra\DataTables\DataTableAbstract
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
     * Affiche la fiche d'une classe avec la liste de ses élèves.
     *
     * @param Classe $classe Classe à afficher
     * @return \Illuminate\View\View
     */
    public function show(Classe $classe)
    {
        $classe->load(['enfants' => function ($q) {
            $q->orderBy('prenom')->orderBy('nom');
        }]);

        return view('admin.classes.show', compact('classe'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle classe.
     *
     * @return \Illuminate\View\View
     */
    public function create()
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
     * Enregistre une nouvelle classe et attribue les enfants sélectionnés.
     *
     * @param Request $request Données du formulaire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom'      => 'required|string|max:255',
            'niveau'   => 'required|string|max:50',
            'children' => 'required|array',
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
     * Affiche le formulaire d’édition d’une classe existante.
     *
     * @param Classe $classe Classe à modifier
     * @return \Illuminate\View\View
     */
    public function edit(Classe $classe)
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
     * Met à jour une classe et synchronise les enfants associés.
     *
     * @param Request $request Données modifiées
     * @param Classe  $classe  Classe à mettre à jour
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Classe $classe)
    {
        $request->validate([
            'nom'      => 'required|string|max:255',
            'niveau'   => 'required|string|max:50',
            'children' => 'required|array',
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

            if (!empty($toDetach)) {
                Enfant::whereIn('idEnfant', $toDetach)->update(['idClasse' => null]);
            }

            if (!empty($toAttach)) {
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
