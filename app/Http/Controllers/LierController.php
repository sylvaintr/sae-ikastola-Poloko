<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LierController extends Controller
{

    /**
     * Methode pour mettre à jour la parité d'un lien entre un parent et une famille
     * @param Request $request la requête HTTP contenant les données de mise à jour de la parité
     * @return JsonResponse la réponse JSON indiquant le résultat de l'opération
     */
    public function updateParite(Request $request): JsonResponse
    {
        $request->validate([
            'idFamille'     => 'required|integer|exists:famille,idFamille',
            'idUtilisateur' => 'required|integer|exists:utilisateur,idUtilisateur',
            'parite'        => 'required|numeric|min:0|max:100',
        ]);

        $idFamille      = $request->idFamille;
        $idParent1      = $request->idUtilisateur;
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
     * @param int $idFamille l'identifiant de la famille pour laquelle la parité est mise à jour
     * @param int $idParent1 l'identifiant du parent pour lequel la parité est mise à jour
     * @param float $nouvelleParite la nouvelle parité pour le parent spécifié
     * @return JsonResponse|null la réponse JSON indiquant l'erreur si la validation échoue, ou null si la validation réussit
     */
    private function validatePariteRequest(int $idFamille, int $idParent1, float $nouvelleParite): ?JsonResponse
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
     * @param int $idFamille l'identifiant de la famille pour laquelle le lien est vérifié
     * @param int $idParent1 l'identifiant du parent pour lequel le lien est vérifié
     * @return JsonResponse|null la réponse JSON indiquant l'erreur si le lien n'existe pas, ou null si le lien existe
     */
    private function validateLinkExists(int $idFamille, int $idParent1): ?JsonResponse
    {
        $exists = DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->exists();

        if (! $exists) {
            return response()->json(['message' => 'Lien introuvable'], 404);
        }

        return null;
    }

    /**
     * Valide le nombre de parents et la parité pour un seul parent.
     * @param int $idFamille l'identifiant de la famille pour laquelle le nombre de parents est vérifié
     * @param float $nouvelleParite la nouvelle parité pour le parent spécifié
     * @return JsonResponse|null la réponse JSON indiquant l'erreur si la validation échoue, ou null si la validation réussit
     */
    private function validateParentsCount(int $idFamille, float $nouvelleParite): ?JsonResponse
    {
        $nombreParents = $this->getNombreParents($idFamille);
        if ($nombreParents === 0) {
            return response()->json(['message' => 'Aucun parent trouvé pour cette famille'], 404);
        }

        if ($nombreParents === 1 && $nouvelleParite != 100) {
            return response()->json([
                'message' => 'Pour un seul parent, la parité doit être de 100%',
                'error'   => 'INVALID_PARITE',
            ], 422);
        }

        return null;
    }

    /**
     * Valide la valeur de la parité.
     * @param float $nouvelleParite la nouvelle parité pour le parent spécifié
     * @return JsonResponse|null la réponse JSON indiquant l'erreur si la parité est invalide, ou null si la parité est valide
     */
    private function validatePariteValue(float $nouvelleParite): ?JsonResponse
    {
        $reste = 100 - $nouvelleParite;
        if ($reste < 0) {
            return response()->json([
                'message' => 'La parité ne peut pas dépasser 100%',
                'error'   => 'INVALID_PARITE',
            ], 422);
        }

        return null;
    }

    /**
     * Récupère le nombre de parents d'une famille.
     * @param int $idFamille l'identifiant de la famille pour laquelle le nombre de parents est récupéré
     * @return int le nombre de parents dans la famille spécifiée
     */
    private function getNombreParents(int $idFamille): int
    {
        return DB::table('lier')
            ->where('idFamille', $idFamille)
            ->count();
    }

    /**
     * Gère la mise à jour de parité pour un seul parent.
     * @param int $idFamille l'identifiant de la famille pour laquelle la parité est mise à jour
     * @param int $idParent1 l'identifiant du parent pour lequel la parité est mise à jour
     * @return JsonResponse la réponse JSON contenant le message de succès après la mise à jour de la parité
     */
    private function handleSingleParent(int $idFamille, int $idParent1): JsonResponse
    {
        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => 100]);

        return response()->json(['message' => 'Parité mise à jour : 100%']);
    }

    /**
     * Gère la mise à jour de parité pour plusieurs parents.
     * @param int $idFamille l'identifiant de la famille pour laquelle les parités sont mises à jour
     * @param int $idParent1 l'identifiant du parent pour lequel la parité est mise à jour
     * @param float $nouvelleParite la nouvelle parité pour le parent spécifié
     * @param int $nombreParents le nombre total de parents dans la famille
     * @return JsonResponse la réponse JSON contenant le message de succès et les parités finales après la mise à jour
     */
    private function handleMultipleParents(int $idFamille, int $idParent1, float $nouvelleParite, int $nombreParents): JsonResponse
    {
        DB::table('lier')
            ->where('idFamille', $idFamille)
            ->where('idUtilisateur', $idParent1)
            ->update(['parite' => $nouvelleParite]);

        $reste               = 100 - $nouvelleParite;
        $nombreAutresParents = $nombreParents - 1;
        $pariteAutres        = $nombreAutresParents > 0 ? round($reste / $nombreAutresParents, 2) : 0;

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
            $difference         = 100 - $totalActuel;
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
     * @param int $idFamille l'identifiant de la famille pour laquelle les parités ont été mises à jour
     * @return JsonResponse la réponse JSON contenant le message de succès et les parités finales
     */
    private function buildSuccessResponse(int $idFamille): JsonResponse
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
            'parites' => $paritesFinales,
        ]);
    }
}
