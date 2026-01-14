<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Recette;

class RecetteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_recette_factory_creates_record()
    {
        // given
        // none

        // when
        $recette = Recette::factory()->create();

        // then
        $this->assertDatabaseHas('recette', ['idRecette' => $recette->idRecette]);
        $this->assertNotNull($recette->evenement());
    }
}
