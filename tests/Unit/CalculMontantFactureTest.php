<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Activite;
use App\Models\Etre;
use Carbon\Carbon;

class CalculMontantFactureTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_enfants_retourne_montants_zero()
    {
        $famille = $this->createFamille(['aineDansAutreSeaska' => false]);
        $facture = $this->createFacture($famille, ['previsionnel' => false]);

        $result = $this->invokeCalculerMontantFacture($facture);

        $this->assertEquals(0, $result['montantcotisation']);
        $this->assertEquals(0, $result['montantparticipation']);
        $this->assertEquals(0, $result['montantparticipationSeaska']);
        $this->assertEquals(0, $result['montangarderie']);
        $this->assertEquals(0, $result['montanttotal']);
    }

    public function test_un_enfant_previsionnel_utilise_nbFoisGarderie()
    {
        $famille = $this->createFamille(['aineDansAutreSeaska' => false]);

        $this->createEnfant($famille, ['nbFoisGarderie' => 9]);

        $facture = $this->createFacture($famille, ['previsionnel' => true]);

        $result = $this->invokeCalculerMontantFacture($facture);

        // 1 enfant -> cotisation 45
        $this->assertEquals(45, $result['montantcotisation']);
        // participation = 1 * 9.65
        $this->assertEquals(9.65, $result['montantparticipation']);
        // nbFoisGarderie = 9 -> >8 => montangarderie 20
        $this->assertEquals(20, $result['montangarderie']);
        // seaska false -> 0
        $this->assertEquals(0, $result['montantparticipationSeaska']);
    }

    public function test_deux_enfants_avec_seaska_et_etre_comptes()
    {
        $famille = $this->createFamille(['aineDansAutreSeaska' => true]);
        $this->createEnfants($famille, 2);

        $facture = $this->createFacture($famille, ['previsionnel' => false]);

        $activite = $this->createGarderieActivity('garderie soir');

        $this->createPresencesForAllEnfants($famille, $activite, Carbon::now());

        $result = $this->invokeCalculerMontantFacture($facture);

        // 2 enfants -> cotisation 65
        $this->assertEquals(65, $result['montantcotisation']);
        // participation = 2 * 9.65
        $this->assertEquals(2 * 9.65, $result['montantparticipation']);
        // seaska true -> 7.70
        $this->assertEquals(7.70, $result['montantparticipationSeaska']);
        // each enfant had 1 presence -> each contributes 10 -> total 20
        $this->assertEquals(20, $result['montangarderie']);
    }

    public function test_trois_ou_plus_enfants_cotisation_75()
    {
        $famille = $this->createFamille(['aineDansAutreSeaska' => false]);

        $this->createEnfants($famille, 3);

        $facture = $this->createFacture($famille, ['previsionnel' => false]);

        $result = $this->invokeCalculerMontantFacture($facture);

        $this->assertEquals(75, $result['montantcotisation']);
    }

    // Helper methods to reduce duplication across tests
    private function createFamille(array $attrs = []): Famille
    {
        return Famille::factory()->create(array_merge(['aineDansAutreSeaska' => false], $attrs));
    }

    private function createEnfant(Famille $famille, array $attrs = []): Enfant
    {
        return Enfant::factory()->create(array_merge(['idFamille' => $famille->idFamille], $attrs));
    }

    private function createEnfants(Famille $famille, int $count = 1, array $attrs = [])
    {
        Enfant::factory()->count($count)->create(array_merge(['idFamille' => $famille->idFamille], $attrs));
    }

    private function createFacture(Famille $famille, array $attrs = []): Facture
    {
        return Facture::factory()->create(array_merge([
            'idFamille' => $famille->idFamille,
            'previsionnel' => true,
            'dateC' => Carbon::now(),
        ], $attrs));
    }

    private function createGarderieActivity(string $name): Activite
    {
        return Activite::factory()->create(['activite' => $name]);
    }

    private function createPresencesForAllEnfants(Famille $famille, Activite $activite, Carbon $date)
    {
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();
        foreach ($enfants as $enfant) {
            Etre::create([
                'idEnfant' => $enfant->idEnfant,
                'activite' => $activite->activite,
                'dateP' => $date,
            ]);
        }
    }

    private function invokeCalculerMontantFacture(Facture $facture)
    {
        $calculator = new \App\Services\FactureCalculator();
        return $calculator->calculerMontantFacture($facture->idFacture);
    }
}
