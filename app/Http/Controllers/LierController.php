<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LierController extends Controller
{
    //  Lier un utilisateur à une famille
    public function store(Request $request)
    {
        $request->validate([
            'idUtilisateur' => 'required|integer|exists:utilisateur,idUtilisateur',
            'idFamille' => 'required|integer|exists:famille,idFamille',
            'parite' => 'nullable|string|max:50'
        ]);

        DB::table('lier')->insert([
            'idUtilisateur' => $request->idUtilisateur,
            'idFamille' => $request->idFamille,
            'parite' => $request->parite
        ]);

        return response()->json(['message' => 'Lien ajouté'], 201);
    }

    //  Supprimer un lien
    public function destroy($idUtilisateur, $idFamille)
    {
        DB::table('lier')->where('idUtilisateur', $idUtilisateur)->where('idFamille', $idFamille)->delete();

        return response()->json(['message' => 'Lien supprimé']);
    }
}

