<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\PRATIQUE;
use App\Models\Enfant;
use App\Models\Activite;

class EtreTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_activite_et_enfant()
    {
        // given
        $activite = Activite::factory()->create(['activite' => 'garderie-test']);
        $enfant = Enfant::factory()->create(['idEnfant' => random_int(10000, 99999)]);

        // when
        $pratiquer = PRATIQUE::create([
            'idEnfant' => $enfant->idEnfant,
            'activite' => $activite->activite,
            'dateP' => now(),
        ]);

        // then
        $this->assertEquals($activite->activite, $etre->activite()->first()->activite);
        $this->assertEquals($enfant->idEnfant, $etre->enfant->idEnfant);
    }
}
