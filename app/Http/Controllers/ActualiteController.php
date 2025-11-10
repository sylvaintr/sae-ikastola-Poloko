<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $utilisateurs = Utilisateur::orderBy('nom')->get();

        return view('actualites.create', compact('utilisateurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'titre' => 'required|string|max:30',
            'description' => 'required|string|max:100',
            'type' => 'required|string|in:Privée,Publique',
            'archive' => 'required|boolean',
            'lien' => 'nullable|string|max:2083',
            'idUtilisateur' => 'required|int',
        ]);
        $actualite = Actualite::create([
            'titre' => $validatedData['titre'],
            'description' => $validatedData['description'],
            'type' => $validatedData['type'],
            'archive' => $validatedData['archive'] ?? false,
            'lien' => $validatedData['lien'],
            'dateP' => date('Y-m-d'),
            'idUtilisateur' => $validatedData['idUtilisateur'],
        ]);
        $actualites = Actualite::orderBy('dateP', 'desc')->get();

        return redirect()->route('home')->with('success', 'Actualité ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Actualite $actualite)
    {
        return 'show';
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Actualite $actualite)
    {
        return 'edit';
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Actualite $actualite)
    {
        return 'update';
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Actualite $actualite)
    {
        return 'delete';
    }
}
