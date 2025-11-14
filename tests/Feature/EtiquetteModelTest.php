<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Etiquette;

class EtiquetteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_etiquette_factory_creates_record()
    {
        $etiquette = Etiquette::factory()->create();

        $this->assertDatabaseHas('etiquette', ['idEtiquette' => $etiquette->idEtiquette]);
        $this->assertGreaterThanOrEqual(0,  $etiquette->actualites()->count());
    }
}
