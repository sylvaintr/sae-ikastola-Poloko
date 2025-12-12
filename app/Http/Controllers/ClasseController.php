<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Enfant;

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
                $showUrl   = route('admin.classes.show', $classe);
                $editUrl   = route('admin.classes.edit', $classe);
                $deleteUrl = route('admin.classes.destroy', $classe);

                return '
        <a href="' . $showUrl . '" class="text-decoration-none text-black me-2" title="Voir">
            <i class="bi bi-eye-fill"></i>
        </a>

        <a href="' . $editUrl . '" class="text-decoration-none text-black me-2" title="Modifier">
            <i class="bi bi-pencil-fill"></i>
        </a>

        <form action="' . $deleteUrl . '" method="POST" class="d-inline"
            onsubmit="return confirm(\'Supprimer cette classe ?\');">
            ' . csrf_field() . method_field('DELETE') . '
            <button type="submit" class="btn btn-link p-0 text-black" title="Supprimer">
                <i class="bi bi-trash-fill"></i>
            </button>
        </form>
    ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function show(Classe $classe)
    {
        // On charge les élèves de la classe, triés par prénom puis nom
        $classe->load(['enfants' => function ($q) {
            $q->orderBy('prenom')->orderBy('nom');
        }]);

        return view('admin.classes.show', compact('classe'));
    }


    public function create()
    {
        // On ne récupère que les enfants qui n'ont pas de classe
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
            'nom'        => 'required|string|max:255',
            'niveau'     => 'required|string|max:50',
            'children'   => 'required|array',
            'children.*' => 'exists:enfant,idEnfant,idClasse,NULL',
        ]);

        // Création de la classe
        $classe = Classe::create($request->only('nom', 'niveau'));

        // Associer les enfants à cette classe (maj du champ idClasse)
        Enfant::whereIn('idEnfant', $request->children)
            ->update(['idClasse' => $classe->idClasse]);

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a bien été créée.');
    }


    public function edit(Classe $classe)
    {
        // Enfants sans classe OU déjà dans cette classe
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
            'nom'    => 'required|string|max:255',
            'niveau' => 'required|string|max:50',
        ]);

        $classe->update($request->only('nom', 'niveau'));

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a bien été modifiée.');
    }


    public function destroy(Classe $classe)
    {
        $classe->enfants()->update(['idClasse' => null]);

        $classe->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a bien été supprimée.');
    }
}
