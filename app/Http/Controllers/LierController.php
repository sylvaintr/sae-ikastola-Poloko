<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LierController extends Controller
{
    //---------------------------------- modification parité ---------------------------------
    public function updateParite(Request $request)
    {
        $request->validate([
            'idFamille' => 'required|integer|exists:famille,idFamille',
            'idUtilisateur' => 'required|integer|exists:utilisateur,idUtilisateur',
            'parite' => 'required|numeric|min:0|max:100',
        ]);

        $idFamille = $request->idFamille;
        $idParent1 = $request->idUtilisateur;

        // --- CORRECTION : Vérifier si le lien existe AVANT de faire l'update ---
        $exists = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->exists();

        if (! $exists) {
            return response()->json(['message' => 'Lien introuvable'], 404);
        }
        // -----------------------------------------------------------------------

        $partParent1 = $request->parite;
        $partParent2 = 100 - $partParent1;

        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => $partParent1]);

        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', '!=', $idParent1)
            ->update(['parite' => $partParent2]);

        return response()->json([
            'message' => "Répartition mise à jour : {$partParent1}% / {$partParent2}%"
        ]);
    }
}

