<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\PRATIQUE;
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

        $montantCotisation = match ($enfants->count()) {
            0 => 0,
            1 => 45,
            2 => 65,
            default => 75,
        };

        $montantParticipation = $enfants->count() * 9.65;

        $montantParticipationSeaska = $famille->aineDansAutreSeaska ? 7.70 : 0;

        $montantGarderiePrev = 0;
        foreach ($enfants as $enfant) {
            if ($facture->previsionnel) {
                $nbFoisGarderie = (int) ($enfant->nbFoisGarderie ?? 0);
            } else {
                $nbFoisGarderie = PRATIQUE::where('idEnfant', $enfant->idEnfant)
                    ->where('activite', 'like', 'garderie%')
                    ->count();
            }

            if ($nbFoisGarderie > 0 && $nbFoisGarderie <= 8) {
                $montantGarderiePrev += 10;
            } elseif ($nbFoisGarderie > 8) {
                $montantGarderiePrev += 20;
            }
        }

        $montantTotalPrev = $montantGarderiePrev + $montantCotisation + $montantParticipation + $montantParticipationSeaska;

        return [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => $enfants,
            'montantcotisation' => $montantCotisation,
            'montantparticipation' => $montantParticipation,
            'montantparticipationSeaska' => $montantParticipationSeaska,
            'montangarderie' => $montantGarderiePrev,
            'montanttotal' => $montantTotalPrev,
            'totalPrevisionnel' => $montantTotalPrev,
        ];
    }
}
