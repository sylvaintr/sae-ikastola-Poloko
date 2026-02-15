<?php
namespace Tests\Unit;

use App\Models\Activite;
use App\Models\Enfant;
use App\Models\Facture;
use App\Models\Famille;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FactureControllerRegularisationTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculer_regularisation_ajoute_dix_si_nbFois_garderie_entre_un_et_huit()
    {
        // given
        // Setup family, child, activity and an Pratiquer record within the facture month
        $famille = Famille::factory()->create();

        // Ensure the enfant has a concrete primary key value
        $enfant = Enfant::factory()->create([
            'idEnfant'  => 1,
            'idFamille' => $famille->idFamille,
        ]);

        $monthDate = Carbon::now()->subMonth()->startOfMonth();

        // create a facture for that month (non-previsionnel)
        $reg = Facture::factory()->create([
            'idFamille'    => $famille->idFamille,
            'previsionnel' => false,
            'dateC'        => $monthDate,
        ]);

        // create an activite and an etre record (1 occurrence => nbfoisgarderie == 1)
        $activiteKey = 'garderie-test';
        Activite::create(['activite' => $activiteKey, 'dateP' => $monthDate]);

        DB::table('pratiquer')->insert([
            'idEnfant' => $enfant->idEnfant,
            'activite' => $activiteKey,
            'dateP'    => $monthDate->toDateTimeString(),
        ]);

        // Mock FactureCalculator to return zeros so only the garderie logic contributes
        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)
            ->onlyMethods(['calculerMontantFacture'])
            ->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn([
            'montantcotisation'          => 0,
            'montantparticipation'       => 0,
            'montantparticipationSeaska' => 0,
            'totalPrevisionnel'          => 0,
        ]);

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Services\FactureCalculator();
        // use the non-previsionnel facture id as reference
        $res = $ctrl->calculerRegularisation($reg->idFacture);

        // then
        // Expect 10 because nbfoisgarderie == 1 (<=8 and >0)
        $this->assertSame(10.0, $res);
    }
}
