<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;
class FamilleController extends Controller
{

public function ajouter(Request $request)
{
    $data = $request->validate([
        'enfants' => 'array',
        'utilisateurs' => 'array',
    ]);

   
    $famille = Famille::create();

    // la creation des enfants
    foreach ($data['enfants'] ?? [] as $enfant) {
        Enfant::create([
            'nom' => $enfant['nom'],
            'prenom' => $enfant['prenom'],
            'dateN' => $enfant['dateN'],
            'sexe' => $enfant['sexe'],
            'NNI' => $enfant['NNI'],
            'idClasse' => $enfant['idClasse'],
            'idFamille' => $famille->idFamille,
        ]);
    }

    //  Lier les utilisateurs avec des familles
    foreach ($data['utilisateurs'] ?? [] as $userData) {
    if (isset($userData['idUtilisateur'])) {
        // Utilisateur existant, on attache
        $famille->utilisateurs()->attach($userData['idUtilisateur'], [
            'parite' => $userData['parite'] ?? null,
        ]);
    } else {
        // Nouveau utilisateur, on crée puis on attache
        $newUser = Utilisateur::create([
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'mdp' => $userData['mdp'] ?? bcrypt('defaultpassword'),
            'languePref' => $userData['languePref'] ?? 'fr'
        ]);

        $famille->utilisateurs()->attach($newUser->idUtilisateur, [
            'parite' => $userData['parite'] ?? null,
        ]);
    }
}


    // 4️⃣ Charger les relations pour les renvoyer
    $famille->load(['enfants', 'utilisateurs']);

    return response()->json([
        'message' => 'Famille complète créée avec succès',
        'famille' => $famille,
    ], 201);
}


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
    $familles = Famille::with(['enfants', 'utilisateurs'])->get();
   //  return response()->json($familles);
    return view('familles.index', compact('familles'));
   //dd($familles);
}

   
    //  Supprimer une famille
   public function destroy($id)
    {
        $famille = Famille::find($id);

        if (!$famille) {
            return response()->json(['message' => 'Famille non trouvée'], 404);
        }

        // on Supprimer l'enfant qui ont lie avec la famille
        $famille->enfants()->delete();

        // Ici on supprime les liason entre les familles et les utilisateurs
        $famille->utilisateurs()->detach();

        
        $famille->delete();

        return response()->json(['message' => 'Famille et enfants supprimés avec succès']);
    }
   

 public function update(Request $request, $id)
{
    // Récupère la famille avec ses enfants et utilisateurs
    $famille = Famille::with(['enfants', 'utilisateurs'])->find($id);

    if (!$famille) {
        return response()->json(['message' => 'Famille non trouvée'], 404);
    }

    //  Mise à jour des enfants
    if ($request->has('enfants')) {
        foreach ($request->enfants as $enfantData) {
            $enfant = $famille->enfants()->find($enfantData['idEnfant'] ?? null);
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

    //  Mise à jour des utilisateurs (parents) + parité
    if ($request->has('utilisateurs')) {
        foreach ($request->utilisateurs as $userData) {
            $userId = $userData['idUtilisateur'] ?? null;
            if (!$userId) continue;

            $user = $famille->utilisateurs()->find($userId);
            if ($user) {
                // Modifier les infos de l'utilisateur
                $user->update([
                    'nom' => $userData['nom'] ?? $user->nom,
                    'prenom' => $userData['prenom'] ?? $user->prenom,
                ]);

                //  Modifier la parité dans la table pivot
                if (isset($userData['parite'])) {
                    \DB::table('lier')
                        ->where('idFamille', $famille->idFamille)
                        ->where('idUtilisateur', $userId)
                        ->update(['parite' => $userData['parite']]);
                }
            }
        }
    }

    //  Recharge la famille pour renvoyer les données à jour
    $famille->load(['enfants', 'utilisateurs']);

    return response()->json([
        'message' => 'Famille mise à jour avec succès (enfants + utilisateurs + parité)',
        'famille' => $famille
    ], 200);
}



}
