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
        $nouvelleParite = $request->parite;

        // Vérifier si le lien existe AVANT de faire l'update
        $exists = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->exists();

        if (! $exists) {
            return response()->json(['message' => 'Lien introuvable'], 404);
        }

        // Récupérer tous les parents de la famille
        $tousLesParents = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->get();

        $nombreParents = $tousLesParents->count();

        if ($nombreParents === 0) {
            return response()->json(['message' => 'Aucun parent trouvé pour cette famille'], 404);
        }

        // Si un seul parent, la parité doit être 100%
        if ($nombreParents === 1) {
            if ($nouvelleParite != 100) {
                return response()->json([
                    'message' => 'Pour un seul parent, la parité doit être de 100%',
                    'error' => 'INVALID_PARITE'
                ], 422);
            }
            
            DB::table('lier')
                ->where('idFamille', $idFamille)
                ->where('idUtilisateur', $idParent1)
                ->update(['parite' => 100]);

            return response()->json([
                'message' => 'Parité mise à jour : 100%'
            ]);
        }

        // Si plusieurs parents, répartir le reste (100% - nouvelleParite) entre les autres
        $reste = 100 - $nouvelleParite;
        $nombreAutresParents = $nombreParents - 1;
        
        if ($reste < 0) {
            return response()->json([
                'message' => 'La parité ne peut pas dépasser 100%',
                'error' => 'INVALID_PARITE'
            ], 422);
        }

        // Mettre à jour la parité du parent spécifié
        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => $nouvelleParite]);

        // Répartir équitablement le reste entre les autres parents
        $pariteAutres = $nombreAutresParents > 0 ? round($reste / $nombreAutresParents, 2) : 0;

        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', '!=', $idParent1)
            ->update(['parite' => $pariteAutres]);

        // Ajuster pour que la somme fasse exactement 100% (gestion des arrondis)
        $totalActuel = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->sum('parite');

        if ($totalActuel != 100) {
            $difference = 100 - $totalActuel;
            // Ajuster le premier parent non modifié
            $premierAutreParent = DB::table('lier')
                ->where('idFamille', $idFamille)
                ->where('idUtilisateur', '!=', $idParent1)
                ->first();

            if ($premierAutreParent) {
                $nouvelleParite = $premierAutreParent->parite + $difference;
                DB::table('lier')
                    ->where('idFamille', $idFamille)
                    ->where('idUtilisateur', $premierAutreParent->idUtilisateur)
                    ->update(['parite' => $nouvelleParite]);
            }
        }

        // Récupérer les parités finales pour le message
        $paritesFinales = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->join('utilisateur', 'lier.idUtilisateur', '=', 'utilisateur.idUtilisateur')
            ->select('utilisateur.nom', 'utilisateur.prenom', 'lier.parite')
            ->get();

        $messageParites = $paritesFinales->map(function ($item) {
            return "{$item->nom} {$item->prenom}: {$item->parite}%";
        })->implode(', ');

        return response()->json([
            'message' => "Répartition mise à jour : {$messageParites}",
            'parites' => $paritesFinales
        ]);
    }
}

