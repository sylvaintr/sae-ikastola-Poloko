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
        // given
        // no setup needed

        // when
        $tache = Tache::factory()->create();

        // then
        $this->assertDatabaseHas('tache', ['idTache' => $tache->idTache]);
        $this->assertGreaterThanOrEqual(0,  $tache->realisateurs()->count());
        $this->assertNotNull($tache->evenement());
    }
}
