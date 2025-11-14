<?php

namespace Tests\Feature\Facture;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Activite;
use App\Models\Etre;
use App\Models\Role;
use App\Models\Utilisateur;
use Carbon\Carbon;

class FactureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_calculates_amounts_correctly()
    {
        // Create family without factory side-effects and set aineDansAutreSeaska = true
        $famille = Famille::create(['aineDansAutreSeaska' => true]);

        // Create exactly 2 enfants for this famille
        Enfant::factory()->create(['idFamille' => $famille->idFamille]);
        Enfant::factory()->create(['idFamille' => $famille->idFamille]);

        $facture = Facture::factory()->create([
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
            'dateC' => Carbon::now(),
            'etat' => false,
        ]);

        // Create an activity that contains 'garderie' in its name
        $activite = Activite::factory()->create(['activite' => 'garderie matin']);

        // For each enfant, create one Etre within the facture month to count as 1 garderie
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();
        foreach ($enfants as $enfant) {
            Etre::create([
                'idEnfant' => $enfant->idEnfant,
                'activite' => $activite->activite,
                'dateP' => Carbon::now(),
            ]);
        }

        // Call the private calculerMontantFacture method directly to avoid view rendering side-effects
        $controller = new \App\Http\Controllers\FactureController();
        $ref = new \ReflectionMethod($controller, 'calculerMontantFacture');
        $ref->setAccessible(true);
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
}
