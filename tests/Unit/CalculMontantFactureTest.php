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

    public function test_zero_children_returns_zero_amounts()
    {
        $famille = Famille::create(['aineDansAutreSeaska' => false]);

        $facture = Facture::factory()->create([
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
            'dateC' => Carbon::now(),
        ]);

        $controller = new \App\Http\Controllers\FactureController();
        $ref = new \ReflectionMethod($controller, 'calculerMontantFacture');

        $result = $ref->invoke($controller, $facture->idFacture);

        $this->assertEquals(0, $result['montantcotisation']);
        $this->assertEquals(0, $result['montantparticipation']);
        $this->assertEquals(0, $result['montantparticipationSeaska']);
        $this->assertEquals(0, $result['montangarderie']);
        $this->assertEquals(0, $result['montanttotal']);
    }

    public function test_one_child_previsionnel_uses_nbFoisGarderie()
    {
        $famille = Famille::create(['aineDansAutreSeaska' => false]);

        Enfant::factory()->create([
            'idFamille' => $famille->idFamille,
            'nbFoisGarderie' => 9,
        ]);

        $facture = Facture::factory()->create([
            'idFamille' => $famille->idFamille,
            'previsionnel' => true,
            'dateC' => Carbon::now(),
        ]);

        $controller = new \App\Http\Controllers\FactureController();
        $ref = new \ReflectionMethod($controller, 'calculerMontantFacture');

        $result = $ref->invoke($controller, $facture->idFacture);

        // 1 enfant -> cotisation 45
        $this->assertEquals(45, $result['montantcotisation']);
        // participation = 1 * 9.65
        $this->assertEquals(9.65, $result['montantparticipation']);
        // nbFoisGarderie = 9 -> >8 => montangarderie 20
        $this->assertEquals(20, $result['montangarderie']);
        // seaska false -> 0
        $this->assertEquals(0, $result['montantparticipationSeaska']);
    }

    public function test_two_children_with_seaska_and_etre_counts()
    {
        $famille = Famille::create(['aineDansAutreSeaska' => true]);

        Enfant::factory()->create(['idFamille' => $famille->idFamille]);
        Enfant::factory()->create(['idFamille' => $famille->idFamille]);

        $facture = Facture::factory()->create([
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
            'dateC' => Carbon::now(),
        ]);

        $activite = Activite::factory()->create(['activite' => 'garderie soir']);

        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();
        foreach ($enfants as $enfant) {
            Etre::create([
                'idEnfant' => $enfant->idEnfant,
                'activite' => $activite->activite,
                'dateP' => Carbon::now(),
            ]);
        }

        $controller = new \App\Http\Controllers\FactureController();
        $ref = new \ReflectionMethod($controller, 'calculerMontantFacture');

        $result = $ref->invoke($controller, $facture->idFacture);

        // 2 enfants -> cotisation 65
        $this->assertEquals(65, $result['montantcotisation']);
        // participation = 2 * 9.65
        $this->assertEquals(2 * 9.65, $result['montantparticipation']);
        // seaska true -> 7.70
        $this->assertEquals(7.70, $result['montantparticipationSeaska']);
        // each enfant had 1 presence -> each contributes 10 -> total 20
        $this->assertEquals(20, $result['montangarderie']);
    }

    public function test_three_or_more_children_cotisation_75()
    {
        $famille = Famille::create(['aineDansAutreSeaska' => false]);

        Enfant::factory()->count(3)->create(['idFamille' => $famille->idFamille]);

        $facture = Facture::factory()->create([
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
            'dateC' => Carbon::now(),
        ]);

        $controller = new \App\Http\Controllers\FactureController();
        $ref = new \ReflectionMethod($controller, 'calculerMontantFacture');

        $result = $ref->invoke($controller, $facture->idFacture);

        $this->assertEquals(75, $result['montantcotisation']);
    }
}
