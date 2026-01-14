<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Evenement;

class EvenementModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_evenement_factory_creates_record()
    {
        // given
        // none

        // when
        $evenement = Evenement::factory()->create();

        // then
        $this->assertDatabaseHas('evenement', ['idEvenement' => $evenement->idEvenement]);
        $this->assertGreaterThanOrEqual(0,  $evenement->recettes()->count());
        $this->assertGreaterThanOrEqual(0,  $evenement->taches()->count());
        $this->assertGreaterThanOrEqual(0,  $evenement->materiels()->count());
    }
}
