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
        $famille = Famille::factory()->create();

        // existing utilisateur that should be updated
        $user = Utilisateur::factory()->create([
            'languePref' => 'fr',
        ]);

        // Attacher l'utilisateur à la famille pour qu'il puisse être mis à jour
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 50]);

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

        // then
        $this->assertEquals(200, $response->getStatusCode());

        $user->refresh();
        $this->assertEquals('eus', $user->languePref);

        // Le comportement actuel (FamilleSynchronizationTrait) ignore les entrées sans idUtilisateur
        // lors d'une mise à jour (update) - il ne crée PAS de nouveaux utilisateurs.
        $this->assertDatabaseMissing('utilisateur', ['nom' => 'ShouldBe', 'prenom' => 'Skipped']);
    }
}
