<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\Etre;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class FactureCalculator
{

    /**
     * Calculer les montants d'une facture donnée.
     * @param string $id Identifiant de la facture à calculer
     * @return array<int, float> tableau associatif contenant les montants calculés :
     *      montantcotisation, montantparticipation, montantparticipationSeaska, montangarderie, montanttotal,facture, famille, enfants
     */
    public function calculerMontantFacture(string $id): array|RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $famille = $facture->famille()->first();
        $enfants = $famille->enfants()->get();

        $montantcotisation = match ($enfants->count()) {
            0 => 0,
            1 => 45,
            2 => 65,
            default => 75,
        };

        $montantparticipation = $enfants->count() * 9.65;

        $montantparticipationSeaska = $famille->aineDansAutreSeaska ? 7.70 : 0;

        $montangarderieprev = 0;

        foreach ($enfants as $enfant) {
            if ($facture->previsionnel) {
                $nbfoisgarderie = (int) ($enfant->nbFoisGarderie ?? 0);
            } else {
                $nbfoisgarderie = Etre::where('idEnfant', $enfant->idEnfant)
                    ->where('activite', 'like', 'garderie%')
                    ->count();
            }

            if ($nbfoisgarderie > 0 && $nbfoisgarderie <= 8) {
                $montangarderieprev += 10;
            } elseif ($nbfoisgarderie > 8) {
                $montangarderieprev += 20;
            }
        }

        $montanttotalprev = $montangarderieprev + $montantcotisation + $montantparticipation + $montantparticipationSeaska;

        return [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => $enfants,
            'montantcotisation' => $montantcotisation,
            'montantparticipation' => $montantparticipation,
            'montantparticipationSeaska' => $montantparticipationSeaska,
            'montangarderie' => $montangarderieprev,
            'montanttotal' => $montanttotalprev,
            'totalPrevisionnel' => $montanttotalprev,
        ];
    }
}
