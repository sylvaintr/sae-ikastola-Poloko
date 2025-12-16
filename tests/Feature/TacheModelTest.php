<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Tache;

class TacheModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tache_factory_creates_record()
    {
        $tache = Tache::factory()->create();

        $this->assertDatabaseHas('tache', ['idTache' => $tache->idTache]);
        $this->assertGreaterThanOrEqual(0,  $tache->realisateurs()->count());
        $this->assertNotNull($tache->evenement());
    }
}
