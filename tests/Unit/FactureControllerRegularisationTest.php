<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Facture;
use App\Models\Activite;
use Carbon\Carbon;

class FactureControllerRegularisationTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculer_regularisation_ajoute_dix_si_nbFois_garderie_entre_un_et_huit()
    {
        // given
        // Setup family, child, activity and an Etre record within the facture month
        $famille = Famille::factory()->create();

        // Ensure the enfant has a concrete primary key value
        $enfant = Enfant::factory()->create([
            'idEnfant' => 1,
            'idFamille' => $famille->idFamille,
        ]);

        $monthDate = Carbon::now()->subMonth()->startOfMonth();

        // create a facture for that month (non-previsionnel)
        $facture = Facture::factory()->create([
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
            'dateC' => $monthDate,
        ]);

        // create an activite and an etre record (1 occurrence => nbfoisgarderie == 1)
        $activiteKey = 'garderie-test';
        Activite::create(['activite' => $activiteKey, 'dateP' => $monthDate]);

        DB::table('etre')->insert([
            'idEnfant' => $enfant->idEnfant,
            'activite' => $activiteKey,
            'dateP' => $monthDate->toDateTimeString(),
        ]);

        // Mock FactureCalculator to return zeros so only the garderie logic contributes
        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)
            ->onlyMethods(['calculerMontantFacture'])
            ->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn([
            'montantcotisation' => 0,
            'montantparticipation' => 0,
            'montantparticipationSeaska' => 0,
            'totalPrevisionnel' => 0,
        ]);

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $res = $ctrl->calculerRegularisation($famille->idFamille);

        // then
        // Expect 10 because nbfoisgarderie == 1 (<=8 and >0)
        $this->assertSame(10, $res);
    }
}
