<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LierController extends Controller
{

    //---------------------------------- modification parité---------------------------------
   public function updateParite(Request $request)
{
    $request->validate([
        'idFamille' => 'required|integer|exists:famille,idFamille',
        'idUtilisateur' => 'required|integer|exists:utilisateur,idUtilisateur',
        'parite' => 'required|string|max:50',
    ]);

    $updated = DB::table('lier')
        ->where('idFamille', $request->idFamille)
        ->where('idUtilisateur', $request->idUtilisateur)
        ->update(['parite' => $request->parite]);

    if ($updated) {
        return response()->json(['message' => 'Parité mise à jour avec succès']);
    }

    return response()->json(['message' => 'Lien non trouvé ou pas de modification'], 404);
}
}

