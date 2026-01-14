<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Enfant;

class EnfantModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_enfant_factory_creates_with_famille_and_classe()
    {
        // given
        // no prior setup required

        // when
        $enfant = Enfant::factory()->create();

        // then
        $this->assertNotNull($enfant->famille);
        $this->assertNotNull($enfant->classe);
    }
}
