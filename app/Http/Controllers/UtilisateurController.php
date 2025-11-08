<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
class UtilisateurController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'prenom' => 'required|string|max:15',
            'nom' => 'required|string|max:15',
            'mdp' => 'required|string|max:20',
            'languePref' => 'required|string|max:17',
            'statutValidation' => 'required|boolean',
        ]);

        $user = Utilisateur::create([
            'prenom' => $request->prenom,
            'nom' => $request->nom,
            'mdp' => bcrypt($request->mdp), 
            'languePref' => $request->languePref,
            'statutValidation' => $request->statutValidation,
        ]);

        return response()->json($user, 201);
    }
// Rechercher un utilisateur
 public function searchByNom(Request $request)
{
    $nom = $request->query('nom');

    if (!$nom) {
        return response()->json(['message' => 'Veuillez fournir un nom'], 400);
    }

    $users = Utilisateur::where('nom', 'like', "%{$nom}%")->get();

    if ($users->isEmpty()) {
        return response()->json(['message' => 'Aucun utilisateur trouvé'], 404);
    }

    return response()->json($users);
}


}
