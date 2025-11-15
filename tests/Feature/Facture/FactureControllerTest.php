<?php

namespace Tests\Feature\Facture;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Activite;
use App\Models\Etre;
use Carbon\Carbon;

class FactureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_calculates_amounts_correctly()
    {
        $famille = $this->createFamilleWithEnfants(2, ['aineDansAutreSeaska' => true]);

        $facture = $this->createFactureForFamille($famille, ['previsionnel' => false, 'etat' => false]);

        $activite = $this->createGarderieActivity('garderie matin');

        $this->createPresencesForAllEnfants($famille, $activite, Carbon::now());

        // Call the private calculerMontantFacture method directly to avoid view rendering side-effects
        $controller = new \App\Http\Controllers\FactureController();
        $ref = new \ReflectionMethod($controller, 'calculerMontantFacture');
        $result = $ref->invoke($controller, $facture->idFacture);

        $cotisation = $result['montantcotisation'];
        $participation = $result['montantparticipation'];
        $seaska = $result['montantparticipationSeaska'];
        $garderie = $result['montangarderie'];
        $total = $result['montanttotal'];

        // For 2 enfants, expected cotisation = 65
        $this->assertEquals(65, $cotisation);

        // participation = 2 * 9.65
        $this->assertEquals(2 * 9.65, $participation);

        // seaska is 7.70 because famille has aineDansAutreSeaska = true
        $this->assertEquals(7.70, $seaska);

        // montangarderie: each enfant had 1 presence -> each contributes 10
        $this->assertEquals(20, $garderie);

        // total is the sum
        $expectedTotal = $cotisation + $participation + $seaska + $garderie;
        $this->assertEquals($expectedTotal, $total);
    }

    /**
     * Create a famille and a given number of enfants.
     *
     * @param int $count
     * @param array $familleAttrs
     * @return \App\Models\Famille
     */
    private function createFamilleWithEnfants(int $count = 1, array $familleAttrs = []): Famille
    {
        $famille = Famille::create(array_merge(['aineDansAutreSeaska' => false], $familleAttrs));

        for ($i = 0; $i < $count; $i++) {
            Enfant::factory()->create(['idFamille' => $famille->idFamille]);
        }

        return $famille;
    }

    /**
     * Create a Facture for the given famille with optional overrides.
     *
     * @param Famille $famille
     * @param array $attrs
     * @return \App\Models\Facture
     */
    private function createFactureForFamille(Famille $famille, array $attrs = []): Facture
    {
        return Facture::factory()->create(array_merge([
            'idFamille' => $famille->idFamille,
            'previsionnel' => true,
            'dateC' => Carbon::now(),
            'etat' => true,
        ], $attrs));
    }

    /**
     * Create an activity with provided name.
     */
    private function createGarderieActivity(string $name): Activite
    {
        return Activite::factory()->create(['activite' => $name]);
    }

    /**
     * Create one Etre presence for each enfant of the famille using given activity.
     */
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
}
