<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActualiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $actualites = Actualite::orderBy('dateP', 'desc')->get();

        return view('index', compact('actualites'));
    }

    public function actualitesAdmin()
    {
        return view('admin.actualites.index');
    }

    public function getDatatable(Request $request)
    {
        if($request->ajax()){
            return DataTables::of(Actualite::query())
                ->editColumn('dateP', function ($row) {
                    return \Carbon\Carbon::parse($row->dateP)->format('d/m/Y');
                })
                ->editColumn('archive', function ($row) {
                    return $row->archive ? 'Archivé' : 'Publié';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('actualite-show', $row);
                    $editUrl = route('admin.actualites.edit', $row);
                    $deleteUrl = route('admin.actualites.delete', $row);
                
                    return '
                        <a href="'.$showUrl.'" style="color: black;"><i class="bi bi-eye"></i></a>
                        <a href="'.$editUrl.'" style="color: black;"><i class="bi bi-pencil-fill"></i></a>
                
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" style="border: none; padding: 0px"
                                onclick="return confirm(\'Supprimer cette actualité ?\')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.actualites.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $utilisateurs = Utilisateur::orderBy('nom')->get();

        return view('admin.actualites.create', compact('utilisateurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'titre' => 'required|string|max:30',
            'description' => 'required|string|max:100',
            'contenu' => 'required',
            'type' => 'required|string|in:Privée,Publique',
            'archive' => 'required|boolean',
            'lien' => 'nullable|string|max:2083',
        ]);
        Actualite::create([
            'titre' => $validatedData['titre'],
            'description' => $validatedData['description'],
            'contenu' => $validatedData['contenu'],
            'type' => $validatedData['type'],
            'archive' => $validatedData['archive'] ?? false,
            'lien' => $validatedData['lien'],
            'dateP' => date('Y-m-d'),
            'idUtilisateur' => auth()->id(),
        ]);

        return redirect()->route('admin.actualites.index')->with('success', 'Actualité ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Actualite $actualite)
    {
        return view('actualite-show', compact('actualite'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Actualite $actualite)
    {
        $utilisateurs = Utilisateur::orderBy('nom')->get();
        return view('admin.actualites.edit', compact('actualite', 'utilisateurs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Actualite $actualite)
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:30',
                'description' => 'required|string|max:100',
                'contenu' => 'required',
                'type' => 'required|string|in:Privée,Publique',
                'archive' => 'required|boolean',
                'lien' => 'nullable|string|max:2083',
            ]);
        
            $actualite->update($validated);
        
            return redirect()->route('admin.actualites.index')->with('success', 'Actualité mise à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Actualite $actualite)
    {
        try {
            $actualite->delete();
            return redirect()
                ->route('admin.actualites.index')
                ->with('success', 'Actualité supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.actualites.index')
                ->with('error', 'Erreur lors de la suppression.');
        }
    }

}
