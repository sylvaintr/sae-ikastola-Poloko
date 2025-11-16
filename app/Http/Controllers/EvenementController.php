<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    /**
     * Afficher tous les événements
     */
    public function index()
    {
        $evenements = Evenement::all();
        return view('evenements.index', compact('evenements'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('evenements.create');
    }

    /**
     * Enregistrement d'un événement
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'obligatoire' => 'required|boolean',
            'dateE' => 'required|date',
        ]);

        Evenement::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'obligatoire' => $request->obligatoire,
            'dateE' => $request->dateE,
        ]);

        return redirect()->route('evenements.index')
                         ->with('success', 'Événement créé avec succès');
    }

    /**
     * Afficher un événement
     */
    public function show($id)
    {
        $evenement = Evenement::findOrFail($id);
        return view('evenements.show', compact('evenement'));
    }

    /**
     * Formulaire d’édition
     */
    public function edit($id)
    {
        $evenement = Evenement::findOrFail($id);
        return view('evenements.edit', compact('evenement'));
    }

    /**
     * Mise à jour d'un événement
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'obligatoire' => 'required|boolean',
            'dateE' => 'required|date',
        ]);

        $evenement = Evenement::findOrFail($id);

        $evenement->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'obligatoire' => $request->obligatoire,
            'dateE' => $request->dateE,
        ]);

        return redirect()->route('evenements.index')
                         ->with('success', 'Événement mis à jour avec succès');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);
        $evenement->delete();

        return redirect()->route('evenements.index')
                         ->with('success', 'Événement supprimé avec succès');
    }
}
