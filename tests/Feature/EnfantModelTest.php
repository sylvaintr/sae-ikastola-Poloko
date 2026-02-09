<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Enfant;
use App\Models\Famille;
use App\Models\Classe;

class EnfantModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_enfant_factory_creates_with_famille_and_classe()
    {
        // given
        // no prior setup required

        // when: create related famille and classe then the enfant
        $famille = Famille::factory()->create();
        $classe = Classe::factory()->create();
        $enfant = Enfant::factory()->create([
            'idFamille' => $famille->idFamille,
            'idClasse' => $classe->idClasse,
        ]);

        // then
        $this->assertNotNull($enfant->famille);
        $this->assertNotNull($enfant->classe);
    }
}
