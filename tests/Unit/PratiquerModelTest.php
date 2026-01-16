<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PRATIQUE;
use App\Models\Activite;
use App\Models\Enfant;

class EtreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relation_activite_resout_avec_la_cle_activite()
    {
        // given
        // none

        // when

        // then
        $activite = Activite::factory()->create(['activite' => 'garderie-unique']);
        $enfant = Enfant::factory()->create(['idEnfant' => random_int(10000, 99999)]);

        $pratiquer = PRATIQUE::create([
            'idEnfant' => $enfant->idEnfant,
            'activite' => $activite->activite,
            'dateP' => now(),
        ]);

        $this->assertNotNull($pratiquer);
        $related = $pratiquer->activite()->first();
        $this->assertNotNull($related);
        $this->assertEquals('garderie-unique', $related->activite);
    }
}
