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
        // given
        // none

        // when
        $activite = Activite::factory()->create();

        // then
        $this->assertDatabaseHas('activite', ['activite' => $activite->activite, 'dateP' => $activite->dateP]);
        $this->assertGreaterThanOrEqual(0,  $activite->pratiquers()->count());
    }
}
