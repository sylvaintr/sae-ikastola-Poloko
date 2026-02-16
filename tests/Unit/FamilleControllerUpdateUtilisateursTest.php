<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Famille;
use App\Models\Utilisateur;

class FamilleControllerUpdateUtilisateursTest extends TestCase
{
    use RefreshDatabase;

    public function test_mise_a_jour_utilisateurs_ignore_les_entrees_sans_idUtilisateur()
    {
        // given
        // none

        // when

        // then
        $famille = Famille::factory()->create();

        // existing utilisateur that should be updated
        $user = Utilisateur::factory()->create([
            'idUtilisateur' => 777,
            'languePref' => 'fr',
        ]);

        $requestData = [
            'enfants' => [],
            'utilisateurs' => [
                // entry without idUtilisateur should be skipped (continue)
                [
                    'nom' => 'ShouldBe',
                    'prenom' => 'Skipped',
                    'mdp' => 'secret',
                ],
                // entry with idUtilisateur should update existing user
                [
                    'idUtilisateur' => $user->idUtilisateur,
                    'languePref' => 'eus',
                ],
            ],
        ];

        $request = Request::create('/', 'PUT', $requestData);

        $controller = new \App\Http\Controllers\FamilleController();
        $response = $controller->update($request, $famille->idFamille);

        $this->assertEquals(200, $response->getStatusCode());

        $user->refresh();
        $this->assertEquals('eus', $user->languePref);

        // Current implementation creates a new utilisateur when no idUtilisateur is provided.
        $this->assertDatabaseHas('utilisateur', ['nom' => 'ShouldBe', 'prenom' => 'Skipped']);
    }
}
