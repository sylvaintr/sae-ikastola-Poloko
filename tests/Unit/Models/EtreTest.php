<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Etre;
use App\Models\Enfant;
use App\Models\Activite;

class EtreTest extends TestCase
{
    use RefreshDatabase;

    public function test_activite_and_enfant_relations()
    {
        $activite = Activite::factory()->create(['activite' => 'garderie-test']);
        $enfant = Enfant::factory()->create(['idEnfant' => random_int(10000, 99999)]);

        $etre = Etre::create([
            'idEnfant' => $enfant->idEnfant,
            'activite' => $activite->activite,
            'dateP' => now(),
        ]);

        $this->assertEquals($activite->activite, $etre->activite()->first()->activite);
        $this->assertEquals($enfant->idEnfant, $etre->enfant->idEnfant);
    }
}
