<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Pratiquer;
use App\Models\Activite;
use App\Models\Enfant;

class PratiquerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relation_activite_resout_avec_la_cle_activite()
    {
        $activite = Activite::factory()->create(['activite' => 'garderie-unique']);
        $enfant = Enfant::factory()->create(['idEnfant' => random_int(10000, 99999)]);

        $pratiquer = Pratiquer::create([
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
