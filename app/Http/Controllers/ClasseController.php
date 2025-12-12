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
    public function index()
    {
        return view('admin.classes.index');
    }

    public function data(Request $request)
    {
        $query = Classe::query();

        return DataTables::of($query)
            ->addColumn('actions', function ($classe) {
                return view('admin.classes.partials.actions', compact('classe'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }


    public function show(Classe $classe)
    {
        $classe->load(['enfants' => function ($q) {
            $q->orderBy('prenom')->orderBy('nom');
        }]);

        return view('admin.classes.show', compact('classe'));
    }


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



    public function store(Request $request)
    {
        $request->validate([
            'nom'      => 'required|string|max:255',
            'niveau'   => 'required|string|max:50',
            'children' => 'required|array',
            'children.*' => [
                'required',
                Rule::exists('enfant', 'idEnfant')
                    ->whereNull('idClasse'),
            ],
        ]);

        $classe = Classe::create($request->only('nom', 'niveau'));

        Enfant::whereIn('idEnfant', $request->children)
            ->update(['idClasse' => $classe->idClasse]);

        return redirect()
            ->route('admin.classes.index')
            ->with('success', __('classes.created_success'));
    }


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
            // 1) MAJ des infos de la classe
            $classe->update($request->only('nom', 'niveau'));

            // 2) Liste des enfants sélectionnés dans le formulaire
            $selectedIds = $request->input('children', []);

            // 3) Liste des enfants actuellement dans cette classe
            $currentIds = $classe->enfants()->pluck('idEnfant')->toArray();

            // 4) Ceux qu'on enlève de la classe
            $toDetach = array_diff($currentIds, $selectedIds);
            // 5) Ceux qu'on ajoute à la classe
            $toAttach = array_diff($selectedIds, $currentIds);

            if (!empty($toDetach)) {
                Enfant::whereIn('idEnfant', $toDetach)
                    ->update(['idClasse' => null]);
            }

            if (!empty($toAttach)) {
                Enfant::whereIn('idEnfant', $toAttach)
                    ->update(['idClasse' => $classe->idClasse]);
            }
        });

        return redirect()
            ->route('admin.classes.index')
            ->with('success', __('classes.updated_success'));
    }


    public function destroy(Classe $classe)
    {
        $classe->enfants()->update(['idClasse' => null]);

        $classe->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', __('classes.deleted_success'));
    }
}
