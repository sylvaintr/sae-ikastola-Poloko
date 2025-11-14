<?php

namespace Tests\Feature;

use App\Http\Controllers\FamilleController;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FamilleControllerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajouter_creates_family_with_children_and_users(): void
    {
        $classe = Classe::factory()->create();

        $controller = new FamilleController();

        $request = Request::create('/famille/ajouter', 'POST', [
            'enfants' => [
                [
                    'nom' => 'Doe',
                    'prenom' => 'John',
                    'dateN' => '2015-01-01',
                    'sexe' => 'M',
                    'NNI' => 123456789,
                    'idClasse' => $classe->idClasse,
                ],
            ],
            'utilisateurs' => [
                [
                    'nom' => 'Parent',
                    'prenom' => 'Marie',
                    'mdp' => 'password123',
                    'languePref' => 'fr',
                ],
            ],
        ]);

        $response = $controller->ajouter($request);

        $this->assertEquals(201, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('famille', $data);

        $familleId = $data['famille']['idFamille'] ?? ($data['famille']['id'] ?? null);
        $this->assertNotNull($familleId, 'Returned famille id not found in response');

        $this->assertDatabaseHas('famille', ['idFamille' => $familleId]);
        $this->assertDatabaseHas('enfant', ['idFamille' => $familleId]);
    }

    public function test_delete_removes_family_and_children(): void
    {
        $famille = Famille::factory()->create();
        Enfant::factory()->count(2)->create(['idFamille' => $famille->idFamille]);

        $controller = new FamilleController();
        $response = $controller->delete($famille->idFamille);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseMissing('famille', ['idFamille' => $famille->idFamille]);
        $this->assertDatabaseMissing('enfant', ['idFamille' => $famille->idFamille]);
    }

    public function test_update_modifies_children_and_users(): void
    {
        $famille = Famille::factory()->create();
        $enfant = Enfant::factory()->create([
            'idEnfant' => rand(200000, 999999),
            'idFamille' => $famille->idFamille,
        ]);
        $user = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur);

        $controller = new FamilleController();

        $request = Request::create('/famille/update', 'PUT', [
            'enfants' => [
                [
                    'idEnfant' => $enfant->idEnfant,
                    'nom' => 'UpdatedNom',
                ],
            ],
            'utilisateurs' => [
                [
                    'idUtilisateur' => $user->idUtilisateur,
                    'nom' => 'NewNomUser',
                ],
            ],
        ]);

        $response = $controller->update($request, $famille->idFamille);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDatabaseHas('enfant', ['idEnfant' => $enfant->idEnfant, 'nom' => 'UpdatedNom']);
        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $user->idUtilisateur, 'nom' => 'NewNomUser']);
    }
}
