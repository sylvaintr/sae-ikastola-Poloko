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

        $validationError = $this->validatePariteRequest($idFamille, $idParent1, $nouvelleParite);
        if ($validationError) {
            return $validationError;
        }

        $nombreParents = $this->getNombreParents($idFamille);
        if ($nombreParents === 1) {
            return $this->handleSingleParent($idFamille, $idParent1);
        }

        return $this->handleMultipleParents($idFamille, $idParent1, $nouvelleParite, $nombreParents);
    }

    /**
     * Valide la requête de mise à jour de parité.
     */
    private function validatePariteRequest(int $idFamille, int $idParent1, float $nouvelleParite)
    {
        $linkError = $this->validateLinkExists($idFamille, $idParent1);
        if ($linkError) {
            return $linkError;
        }

        $parentError = $this->validateParentsCount($idFamille, $nouvelleParite);
        if ($parentError) {
            return $parentError;
        }

        return $this->validatePariteValue($nouvelleParite);
    }

    /**
     * Valide que le lien existe.
     */
    private function validateLinkExists(int $idFamille, int $idParent1)
    {
        $exists = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Lien introuvable'], 404);
        }

        return null;
    }

    /**
     * Valide le nombre de parents et la parité pour un seul parent.
     */
    private function validateParentsCount(int $idFamille, float $nouvelleParite)
    {
        $nombreParents = $this->getNombreParents($idFamille);
        if ($nombreParents === 0) {
            return response()->json(['message' => 'Aucun parent trouvé pour cette famille'], 404);
        }

        if ($nombreParents === 1 && $nouvelleParite != 100) {
            return response()->json([
                'message' => 'Pour un seul parent, la parité doit être de 100%',
                'error' => 'INVALID_PARITE'
            ], 422);
        }

        return null;
    }

    /**
     * Valide la valeur de la parité.
     */
    private function validatePariteValue(float $nouvelleParite)
    {
        $reste = 100 - $nouvelleParite;
        if ($reste < 0) {
            return response()->json([
                'message' => 'La parité ne peut pas dépasser 100%',
                'error' => 'INVALID_PARITE'
            ], 422);
        }

        return null;
    }

    /**
     * Récupère le nombre de parents d'une famille.
     */
    private function getNombreParents(int $idFamille): int
    {
        return DB::table('lier')
            ->where('idFamille', $idFamille)
            ->count();
    }

    /**
     * Gère la mise à jour de parité pour un seul parent.
     */
    private function handleSingleParent(int $idFamille, int $idParent1)
    {
        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => 100]);

        return response()->json(['message' => 'Parité mise à jour : 100%']);
    }

    /**
     * Gère la mise à jour de parité pour plusieurs parents.
     */
    private function handleMultipleParents(int $idFamille, int $idParent1, float $nouvelleParite, int $nombreParents)
    {
        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => $nouvelleParite]);

        $reste = 100 - $nouvelleParite;
        $nombreAutresParents = $nombreParents - 1;
        $pariteAutres = $nombreAutresParents > 0 ? round($reste / $nombreAutresParents, 2) : 0;

        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', '!=', $idParent1)
            ->update(['parite' => $pariteAutres]);

        $this->adjustPariteTo100($idFamille, $idParent1);

        return $this->buildSuccessResponse($idFamille);
    }

    /**
     * Ajuste la parité pour que la somme fasse exactement 100%.
     */
    private function adjustPariteTo100(int $idFamille, int $idParent1): void
    {
        $totalActuel = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->sum('parite');

        if ($totalActuel != 100) {
            $difference = 100 - $totalActuel;
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
    }

    /**
     * Construit la réponse de succès avec les parités finales.
     */
    private function buildSuccessResponse(int $idFamille)
    {
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

