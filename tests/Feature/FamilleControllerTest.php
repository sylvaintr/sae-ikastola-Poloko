<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;
use App\Models\Utilisateur;
use App\Models\Classe;
use App\Models\Enfant;

class FamilleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_list_of_familles()
    {
        $families = Famille::factory()->count(2)->create();

        $response = $this->getJson('/api/familles2');

        $response->assertOk();
        $data = $response->json();
        $ids = array_column($data, 'idFamille');

        // Ensure the two created families are present in the response
        $this->assertContains($families[0]->idFamille, $ids);
        $this->assertContains($families[1]->idFamille, $ids);
    }

    public function test_show_returns_family_or_404()
    {
        $famille = Famille::factory()->create();

        $response = $this->getJson('/api/familles/' . $famille->idFamille);
        $response->assertOk();
        $response->assertJsonStructure(['idFamille', 'enfants', 'utilisateurs']);

        $missing = $this->getJson('/api/familles/999999999');
        $missing->assertStatus(404);
    }

    public function test_ajouter_creates_family_with_children_and_users()
    {
        $classe = Classe::factory()->create();
        $existingUser = Utilisateur::factory()->create();

        $childId = random_int(1000000, 1999999);

        $payload = [
            'enfants' => [
                [
                    'idEnfant' => $childId,
                    'nom' => 'Dupont',
                    'prenom' => 'Alice',
                    'dateN' => '2015-05-01',
                    'sexe' => 'F',
                    'NNI' => random_int(200000000, 299999999),
                    'idClasse' => $classe->idClasse,
                ]
            ],
            'utilisateurs' => [
                // attach existing
                ['idUtilisateur' => $existingUser->idUtilisateur, 'parite' => 'parent'],
                // create new one (no idUtilisateur)
                ['nom' => 'Martin', 'prenom' => 'Paul', 'mdp' => 'secret', 'languePref' => 'fr', 'parite' => 'tuteur'],
            ],
        ];

        $response = $this->postJson('/api/familles', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('message', 'Famille complète créée avec succès');

        $familleId = $response->json('famille.idFamille');

        $this->assertDatabaseHas('famille', ['idFamille' => $familleId]);
        $this->assertDatabaseHas('enfant', ['idEnfant' => $childId, 'idFamille' => $familleId]);
        $this->assertDatabaseHas('lier', ['idUtilisateur' => $existingUser->idUtilisateur, 'idFamille' => $familleId]);
    }

    public function test_update_modifies_children_and_users()
    {
        $famille = Famille::factory()->create();

        // factory created enfants and lier via configure()
        $enfant = $famille->enfants()->first();
        $utilisateur = $famille->utilisateurs()->first();

        $newChildName = 'UpdatedName';
        $newLang = 'en';

        $payload = [
            'enfants' => [
                [
                    'idEnfant' => $enfant->idEnfant,
                    'nom' => $newChildName,
                ],
            ],
            'utilisateurs' => [
                [
                    'idUtilisateur' => $utilisateur->idUtilisateur,
                    'languePref' => $newLang,
                ],
            ],
        ];

        $response = $this->putJson('/api/familles2/' . $famille->idFamille, $payload);

        $response->assertOk();
        $response->assertJsonPath('message', 'Famille mise à jour (enfants + utilisateurs)');

        $this->assertDatabaseHas('enfant', ['idEnfant' => $enfant->idEnfant, 'nom' => $newChildName]);
        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $utilisateur->idUtilisateur, 'languePref' => $newLang]);
    }

    public function test_delete_removes_family_children_and_detaches_users()
    {
        $famille = Famille::factory()->create();

        $familleId = $famille->idFamille;

        $this->assertGreaterThan(0, $famille->enfants()->count());
        $this->assertGreaterThan(0, $famille->utilisateurs()->count());

        $response = $this->deleteJson('/api/familles/' . $familleId);

        $response->assertOk();
        $response->assertJsonPath('message', 'Famille et enfants supprimés avec succès');

        $this->assertDatabaseMissing('famille', ['idFamille' => $familleId]);
        $this->assertDatabaseMissing('enfant', ['idFamille' => $familleId]);
        $this->assertDatabaseMissing('lier', ['idFamille' => $familleId]);
    }


    public function test_delete_nonexistent_family_returns_404()
    {
        $response = $this->deleteJson('/api/familles/999999999');

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Famille non trouvée');
    }

    public function test_update_famille_nonexistent_returns_404()
    {
        $payload = [
            'enfants' => [],
            'utilisateurs' => [],
        ];

        $response = $this->putJson('/api/familles2/999999999', $payload);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Famille non trouvée');
    }
}
