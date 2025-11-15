<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\FamilleController;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;

class FamilleControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajouter_creates_family_with_children_and_users()
    {
        $controller = new FamilleController();

        $uniqueId = (int) now()->format('U') + random_int(1, 99999);

        $payload = [
            'enfants' => [
                [
                    'idEnfant' => $uniqueId,
                    'nom' => 'Child',
                    'prenom' => 'One',
                    'dateN' => now()->toDateString(),
                    'sexe' => 'M',
                    'idClasse' => 1,
                    'nbFoisGarderie' => 2,
                ],
            ],
            'utilisateurs' => [
                [
                    'nom' => 'Parent',
                    'prenom' => 'Alpha',
                    'mdp' => 'secretpass',
                    'languePref' => 'fr',
                ],
            ],
        ];

        $request = Request::create('/famille/ajouter', 'POST', $payload);

        $response = $controller->ajouter($request);

        $this->assertEquals(201, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('famille', $data);
        $this->assertNotEmpty($data['famille']['enfants']);
        $this->assertNotEmpty($data['famille']['utilisateurs']);
    }

    public function test_show_returns_404_for_missing()
    {
        $controller = new FamilleController();
        $response = $controller->show(999999);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_index_and_delete_and_update_flow()
    {
        $controller = new FamilleController();

        // create famille and children
        $famille = Famille::factory()->create();
        Enfant::factory()->create(['idFamille' => $famille->idFamille]);
        $enfant = Enfant::where('idFamille', $famille->idFamille)->first();
        $user = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 'parent']);

        // index should return familles
        $indexResponse = $controller->index();
        $this->assertEquals(200, $indexResponse->getStatusCode());

        // update: change enfant name and utilisateur lang
        $updatePayload = Request::create('/famille/update', 'PUT', [
            'enfants' => [
                ['idEnfant' => $enfant->idEnfant, 'nom' => 'NewName']
            ],
            'utilisateurs' => [
                ['idUtilisateur' => $user->idUtilisateur, 'nom' => 'Updated']
            ],
        ]);

        $updateResponse = $controller->update($updatePayload, $famille->idFamille);
        $this->assertEquals(200, $updateResponse->getStatusCode());

        $this->assertDatabaseHas('enfant', ['idEnfant' => $enfant->idEnfant, 'nom' => 'NewName']);
        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $user->idUtilisateur, 'nom' => 'Updated']);

        // delete family
        $deleteResponse = $controller->delete($famille->idFamille);
        $this->assertEquals(200, $deleteResponse->getStatusCode());
        $this->assertDatabaseMissing('famille', ['idFamille' => $famille->idFamille]);
    }
}
