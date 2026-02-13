<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Classe;

class FamilleControllerUpdateEnfantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_enfants_ignore_entrees_sans_idEnfant()
    {
        // given
        // none

        // when

        // then
        $famille = Famille::factory()->create();

        // Create an enfant that should be updated
        $enfant = Enfant::factory()->create([
            'idEnfant' => 100999,
            'idFamille' => $famille->idFamille,
            'nom' => 'Before',
            'prenom' => 'Child',
        ]);

        $classe = Classe::factory()->create();

        $requestData = [
            'enfants' => [
                // This entry has no idEnfant; current implementation will create it,
                // so provide required fields for creation.
                [
                    'nom' => 'Skipped',
                    'prenom' => 'NoId',
                    'dateN' => '2020-01-01',
                    'sexe' => 'M',
                    'NNI' => '000',
                    'idClasse' => $classe->idClasse,
                ],
                // This entry should be applied to the existing enfant
                [
                    'idEnfant' => $enfant->idEnfant,
                    'nom' => 'After',
                ],
            ],
            'utilisateurs' => [],
        ];

        $request = Request::create('/', 'PUT', $requestData);

        $controller = new \App\Http\Controllers\FamilleController();
        $response = $controller->update($request, $famille->idFamille);

        $this->assertEquals(200, $response->getStatusCode());

        $enfant->refresh();
        $this->assertEquals('After', $enfant->nom);

        // The implementation creates a new enfant when no idEnfant is provided.
        $this->assertDatabaseHas('enfant', ['nom' => 'Skipped', 'prenom' => 'NoId']);
    }
}
