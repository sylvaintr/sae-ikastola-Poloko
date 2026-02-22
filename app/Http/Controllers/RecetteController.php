<?php

namespace App\Http\Controllers;

use App\Models\Recette;
use App\Models\Evenement;
use Illuminate\Http\Request;

class RecetteController extends Controller
{
    /**
     * Afficher le formulaire de création d'une recette pour un événement
     */
    // Form create non utilisé (store via modal sur show)
    public function create($evenementId)
    {
        $evenement = Evenement::findOrFail($evenementId);
        return view('recettes.create', compact('evenement'));
    }

    /**
     * Enregistrer une nouvelle recette
     */
    public function store(Request $request, Evenement $evenement)
    {
        $request->validate([
            'description' => 'required|string|max:100',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|string|max:50',
            'type' => 'required|string|in:recette,depense,depense_previsionnelle',
        ]);

        // Générer un idRecette unique (puisque incrementing = false)
        $maxId = Recette::max('idRecette') ?? 0;
        $newId = $maxId + 1;

        Recette::create([
            'idRecette' => $newId,
            'description' => $request->description,
            'prix' => $request->prix,
            'quantite' => $request->quantite,
            'type' => $request->type,
            'idEvenement' => $evenement->idEvenement,
        ]);

        return redirect()->route('evenements.show', $evenement)
                         ->with('success', 'Recette ajoutée avec succès');
    }

    /**
     * Afficher le formulaire d'édition d'une recette
     */
    public function edit(Recette $recette)
    {
        return view('recettes.edit', compact('recette'));
    }

    /**
     * Mettre à jour une recette
     */
    public function update(Request $request, Recette $recette)
    {
        $request->validate([
            'description' => 'required|string|max:100',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|string|max:50',
            'type' => 'required|string|in:recette,depense,depense_previsionnelle',
        ]);

        $recette->update([
            'description' => $request->description,
            'prix' => $request->prix,
            'quantite' => $request->quantite,
            'type' => $request->type,
        ]);

        return redirect()->route('evenements.show', $recette->idEvenement)
                         ->with('success', 'Recette mise à jour avec succès');
    }

    /**
     * Supprimer une recette
     */
    public function destroy(Recette $recette)
    {
        $evenementId = $recette->idEvenement;
        $recette->delete();

        return redirect()->route('evenements.show', $evenementId)
                         ->with('success', 'Recette supprimée avec succès');
    }
}
