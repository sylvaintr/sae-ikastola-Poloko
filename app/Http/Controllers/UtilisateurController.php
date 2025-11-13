<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
class UtilisateurController extends Controller
{
   
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
