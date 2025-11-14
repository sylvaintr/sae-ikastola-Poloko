<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Actualite;

class ActualiteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualite_factory_creates_record()
    {
        $actualite = Actualite::factory()->create();

        $this->assertDatabaseHas('actualite', ['idActualite' => $actualite->idActualite]);
        $this->assertGreaterThanOrEqual(0,  $actualite->documents()->count());
        $this->assertGreaterThanOrEqual(0,  $actualite->etiquettes()->count());
        $this->assertNotNull($actualite->utilisateur());
    }
}
