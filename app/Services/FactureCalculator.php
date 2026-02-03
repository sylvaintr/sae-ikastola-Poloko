<?php
namespace App\Services;

use App\Models\Facture;
use App\Models\Famille;
use App\Models\Pratiquer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class FactureCalculator
{

    /**
     * Calculer les montants d'une facture donnée.
     * @param string $id Identifiant de la facture à calculer
     * @return array<int, float> tableau associatif contenant les montants calculés :
     *      montantcotisation, montantparticipation, montantparticipationSeaska, montangarderie, montanttotal,facture, famille, nbEnfants
     */
    public function calculerMontantFacture(string $id): array | RedirectResponse
    {

        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $famille = $facture->famille; // relation property returns model
        if ($famille === null) {
            return redirect()->route('admin.facture.index')->with('error', 'famille.inexistante');
        }

        $enfants = $famille->enfants()->get();

        $montantCotisation = match ($enfants->count()) {
            0       => 0,
            1       => 45,
            2       => 65,
            default => 75,
        };

        $montantParticipation = $enfants->count() * 9.65;

        $montantParticipationSeaska = $famille->aineDansAutreSeaska ? 7.70 : 0;

        $montantGarderiePrev = 0;
        foreach ($enfants as $enfant) {
            if ($facture->previsionnel) {
                $nbFoisGarderie = (int) ($enfant->nbFoisGarderie ?? 0);
            } else {
                $nbFoisGarderie = Pratiquer::where('idEnfant', $enfant->idEnfant)
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
            'facture'                    => $facture,
            'famille'                    => $famille,
            'enfants'                    => $enfants,
            'nbEnfants'                  => $enfants->count(),
            'montantcotisation'          => $montantCotisation,
            'montantparticipation'       => $montantParticipation,
            'montantparticipationSeaska' => $montantParticipationSeaska,
            'montangarderie'             => $montantGarderiePrev,
            'montanttotal'               => $montantTotalPrev,
            'totalPrevisionnel'          => $montantTotalPrev,
        ];
    }

    /**
     * calcule le montant de la regulation  pour une facture donnée
     * @param int $idfamille identifiant de la famille
     * @return int montant de la regulation
     */
    public function calculerRegularisation(int $idfacture): int
    {

        $facture = Facture::find($idfacture);

        // recupere la derniere date de facture non previsionnelle

        if ($facture === null) {
            return 0;
        }
        $lastRegDate = Facture::where('idFamille', $facture->idFamille)
            ->where('idUtilisateur', $facture->idUtilisateur)
            ->where('previsionnel', false)
            ->whereDate('dateC', '<', $facture->dateC)
            ->max('dateC');

        $startDate = $lastRegDate ? Carbon::parse($lastRegDate) : Carbon::create(2000, 1, 1);
        $idFamille = (int)$facture->idFamille;
        // récupération des factures prévisionnelles entre la date de départ et la date de la facture courante
        $facturesPrev = Facture::where('idFamille', $idFamille)
            ->where('previsionnel', true)
            ->where('dateC', '>=', $startDate)
            ->where('dateC', '<=', $facture->dateC)
            ->get();

        // calcul du total prévisionnel
        $totalPrev = 0;
        foreach ($facturesPrev as $facture) {
            $montantDetails  = $this->calculerMontantFacture($facture->idFacture);
            $totalPrev      += $montantDetails['totalPrevisionnel']; // Null coalesce safety
        }

        // calcul du total réel (garderie) entre les mois
        $totalRegularisation = 0;

        
        $enfants = $facture->famille()->enfants()->get();
        $cursorDate = $startDate->copy()->startOfMonth();
        $endDate = $facture->dateC->copy()->endOfMonth();

        while ($cursorDate->lte($endDate)) {
            foreach ($enfants as $enfant) {
                $monthStart = $cursorDate->copy()->startOfMonth();
                $monthEnd = $cursorDate->copy()->endOfMonth();

                $nbfoisgarderie = Pratiquer::where('idEnfant', $enfant->idEnfant)
                    ->whereBetween('dateP', [$monthStart, $monthEnd])
                    ->where('activite', 'like', '%garderie%')
                    ->count();

                if ($nbfoisgarderie > 8) {
                    $totalRegularisation += 20;
                } elseif ($nbfoisgarderie > 0) {
                    $totalRegularisation += 10;
                }
            }
            $cursorDate->addMonth();
        }

        // si c'est positif la famille doit de l'argent
        // si c'est negatif l'ikastola doit de l'argent a la famille
        return $totalRegularisation - $totalPrev;
    }
}
