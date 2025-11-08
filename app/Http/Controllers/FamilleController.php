<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Famille;
use App\Models\Enfant;

class FamilleController extends Controller
{
   // Ajouter une famille
    public function store(Request $request)
{
    
    $famille = Famille::create([
        'idFamille' => $request->idFamille,
    ]);

    return response()->json($famille, 201);
}

// Afficher une famille
  public function show($id)
    {
        $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

        if (!$famille) {
            return response()->json(['message' => 'Famille nest pas trouvée'], 404);
        }

        return response()->json($famille);
    }

    
   public function index()
    {
        return Famille::with(['utilisateurs', 'enfants'])->get();
    }

    //  Supprimer une famille
   public function destroy($id)
    {
        $famille = Famille::find($id);

        if (!$famille) {
            return response()->json(['message' => 'Famille non trouvée'], 404);
        }

        // Supprimer l'enfant qui ont lie avec la famille
        $famille->enfants()->delete();

        // Ici on supprime les liason entre les familles et les utilisateurs
        $famille->utilisateurs()->detach();

        
        $famille->delete();

        return response()->json(['message' => 'Famille et enfants supprimés avec succès']);
    }
   

  public function update(Request $request, $id)
{
    // Récupérer la famille avec enfants et utilisateurs
    $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

    if (!$famille) {
        return response()->json(['message' => 'Famille non trouvée'], 404);
    }

    // Modifier les enfants
    if ($request->has('enfants')) {
        foreach ($request->enfants as $enfantData) {
            $enfant = $famille->enfants()->find($enfantData['idEnfant']);
            if ($enfant) {
                $enfant->update([
                    'nom' => $enfantData['nom'] ?? $enfant->nom,
                    'prenom' => $enfantData['prenom'] ?? $enfant->prenom,
                    'dateN' => $enfantData['dateN'] ?? $enfant->dateN,
                    'sexe' => $enfantData['sexe'] ?? $enfant->sexe,
                    'NNI' => $enfantData['NNI'] ?? $enfant->NNI,
                    'idClasse' => $enfantData['idClasse'] ?? $enfant->idClasse,
                ]);
            }
        }
    }

    // Modifier les utilisateurs et la parité
    if ($request->has('utilisateurs')) {
        foreach ($request->utilisateurs as $userData) {
            $user = $famille->utilisateurs()->find($userData['idUtilisateur']);
            if ($user) {
                // Modifier les infos de l'utilisateur
                $user->update([
                    'nom' => $userData['nom'] ?? $user->nom,
                    'prenom' => $userData['prenom'] ?? $user->prenom,
                ]);

                // Modifier la parité dans la table pivot
                if (isset($userData['parite'])) {
                    $famille->utilisateurs()->updateExistingPivot($user->idUtilisateur, [
                        'parite' => $userData['parite']
                    ]);
                }
            }
        }
    }

    // Recharger la famille avec les relations pour retourner les données à jour
    $famille->load(['enfants', 'utilisateurs']);

    return response()->json([
        'message' => 'Famille et ses relations mises à jour avec succès',
        'famille' => $famille
    ]);
}



}
