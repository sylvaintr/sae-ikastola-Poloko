<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LierController extends Controller
{

    //---------------------------------- modification parité---------------------------------
  public function updateParite(Request $request)
    {
        // 1. Validation
        $request->validate([
            'idFamille' => 'required|integer|exists:famille,idFamille',
            'idUtilisateur' => 'required|integer|exists:utilisateur,idUtilisateur', // C'est le Parent 1
            'parite' => 'required|numeric|min:0|max:100',
        ]);

        $idFamille = $request->idFamille;
        $idParent1 = $request->idUtilisateur;
        
        // 2. Calcul des parts
        $partParent1 = $request->parite;
        $partParent2 = 100 - $partParent1; // Le reste pour l'autre

        // 3. Mise à jour du Parent 1
        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => $partParent1]);

        // 4. Mise à jour automatique du Parent 2
        // "Met à jour celui qui est dans la même famille mais qui n'est PAS le parent 1"
        $updated = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', '!=', $idParent1)
            ->update(['parite' => $partParent2]);

        return response()->json([
            'message' => "Répartition mise à jour : {$partParent1}% / {$partParent2}%"
        ]);
    }
}

