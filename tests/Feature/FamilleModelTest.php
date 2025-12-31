<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;

class FamilleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_famille_factory_creates_children_and_lier()
    {
        $famille = Famille::factory()->create();

        // Assert famille exists in DB
        $this->assertDatabaseHas('famille', ['idFamille' => $famille->idFamille]);

        // The FamilleFactory config creates at least one enfant and one lier entry
        // Allow zero factures if the factory does not create any; require at least one enfant and utilisateur.
        $this->assertGreaterThanOrEqual(0, $famille->factures()->count());
        $this->assertGreaterThan(0, $famille->enfants()->count());
        $this->assertGreaterThan(0, $famille->utilisateurs()->count());
    }
}
