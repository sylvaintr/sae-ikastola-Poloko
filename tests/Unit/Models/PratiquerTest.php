<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pratiquer;
use App\Models\Enfant;
use App\Models\Activite;

class PratiquerTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_activite_et_enfant()
    {
        $activite = Activite::factory()->create(['activite' => 'garderie-test']);
        $enfant = Enfant::factory()->create(['idEnfant' => random_int(10000, 99999)]);

        $pratiquer = Pratiquer::create([
            'idEnfant' => $enfant->idEnfant,
            'activite' => $activite->activite,
            'dateP' => now(),
        ]);

        $this->assertEquals($activite->activite, $pratiquer->activite()->first()->activite);
        $this->assertEquals($enfant->idEnfant, $pratiquer->enfant->idEnfant);
    }
}
