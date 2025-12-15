<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Posseder;

class PossederModelTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_poseder_factory_creates_record()
    {
        $poseder = Posseder::factory()->create();

        $this->assertDatabaseHas('posseder', [
            'idEtiquette' => $poseder->idEtiquette,
            'idRole' => $poseder->idRole,
        ]);
        $this->assertGreaterThanOrEqual(0,  $poseder->etiquette()->count());
        $this->assertGreaterThanOrEqual(0,  $poseder->role()->count());
    }
}
