<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Materiel;

class MaterielModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_materiel_factory_creates_record()
    {
        $materiel = Materiel::factory()->create();

        $this->assertDatabaseHas('materiel', ['idMateriel' => $materiel->idMateriel]);
        $this->assertGreaterThanOrEqual(0,  $materiel->evenements()->count());
    }
}
