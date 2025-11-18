<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Activite;

class ActiviteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_activite_factory_creates_record()
    {
        $activite = Activite::factory()->create();

        $this->assertDatabaseHas('activite', ['activite' => $activite->activite, 'dateP' => $activite->dateP]);
        $this->assertGreaterThanOrEqual(0,  $activite->etres()->count());
    }
}
