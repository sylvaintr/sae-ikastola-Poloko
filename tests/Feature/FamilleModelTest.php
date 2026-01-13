<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;

class FamilleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_famille_factory_created_lier_but_no_children_by_default()
    {
        $famille = Famille::factory()->create();

        // Assert famille exists in DB
        $this->assertDatabaseHas('famille', ['idFamille' => $famille->idFamille]);

        // The FamilleFactory config creates Lier but NO children by default (fix for Unit tests)
        $this->assertEquals(0, $famille->enfants()->count());
        $this->assertGreaterThan(0, $famille->utilisateurs()->count());
    }
}
