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
            $editUrl = route('admin.classes.edit', $classe);
            $deleteUrl = route('admin.classes.destroy', $classe);

            return '
                <a href="'.$editUrl.'" class="text-decoration-none text-black">
                    <i class="bi bi-pencil-fill me-3"></i>
                </a>

                <form action="'.$deleteUrl.'" method="POST" class="d-inline"
                    onsubmit="return confirm(\'Supprimer cette classe ?\');">
                    '.csrf_field().method_field('DELETE').'
                    <button type="submit" class="btn btn-link p-0 text-black">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            ';
        })


            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        // Tous les enfants (comme avant)
        $children = Enfant::orderBy('prenom')->orderBy('nom')->get();

        // ✅ Liste des niveaux déjà existants (distinct)
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
        'children.*' => 'exists:enfant,idEnfant',
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
    // Tous les enfants
    $children = Enfant::orderBy('prenom')->orderBy('nom')->get();

    // Liste des niveaux existants
    $levels = Classe::select('niveau')
        ->distinct()
        ->orderBy('niveau')
        ->pluck('niveau');

    // Enfants déjà associés à cette classe
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
        $classe->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a bien été supprimée.');
    }
}
