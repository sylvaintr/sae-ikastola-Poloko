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
        Famille::factory()->count(3)->create();

        $response = $this->getJson('/api/familles');

        $response->assertStatus(200);
        $json = $response->json();
        $data = isset($json['data']) ? $json['data'] : $json;

        $this->assertGreaterThanOrEqual(3, count($data));
    }

    public function test_show_returns_family()
    {
        $famille = Famille::factory()->create();
        $response = $this->getJson('/api/familles/' . $famille->idFamille);
        $response->assertStatus(200);
        $response->assertJsonFragment(['idFamille' => $famille->idFamille]);
    }

    public function test_show_returns_404_if_missing()
    {
        $response = $this->getJson('/api/familles/999999999');
        $response->assertStatus(404);
    }

    public function test_ajouter_creates_family()
    {
        $classe = Classe::factory()->create();
        $user = Utilisateur::factory()->create(['email' => 'parent1.'.uniqid().'@test.com']);

        $payload = [
            'enfants' => [
                [
                    'nom' => 'Dupont',
                    'prenom' => 'Alice',
                    'dateN' => '2015-05-01',
                    'sexe' => 'F',
                    'NNI' => '123456789',
                    'idClasse' => $classe->idClasse,
                ]
            ],
            'utilisateurs' => [
                ['idUtilisateur' => $user->idUtilisateur, 'parite' => 100]
            ]
        ];

        $response = $this->postJson('/api/familles', $payload);
        $response->assertStatus(201);
        
        $idFamille = $response->json('famille.idFamille');
        $this->assertDatabaseHas('famille', ['idFamille' => $idFamille]);
        $this->assertDatabaseHas('enfant', ['nom' => 'Dupont', 'idFamille' => $idFamille]);
    }

    public function test_update_modifies_family()
    {
        // 1. Création initiale
        $famille = Famille::factory()->create();
        
        // On crée l'enfant, mais on le recharge immédiatement depuis la BDD
        // pour être SÛR d'avoir son idEnfant (car le modèle est mal configuré)
        Enfant::factory()->create(['idFamille' => $famille->idFamille, 'nom' => 'Ancien']);
        $enfant = Enfant::where('idFamille', $famille->idFamille)->where('nom', 'Ancien')->first();

        // Idem pour l'utilisateur
        $user = Utilisateur::factory()->create(['email' => 'parent2.'.uniqid().'@test.com']);
        // On recharge l'utilisateur pour être sûr d'avoir l'ID
        $user = Utilisateur::where('email', $user->email)->first();

        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 100]);

        // 2. Modification
        $response = $this->putJson('/api/familles/' . $famille->idFamille, [
            'enfants' => [
                [
                    'idEnfant' => $enfant->idEnfant, // Maintenant, ceci n'est plus null !
                    'nom' => 'NomModifie'
                ]
            ],
            'utilisateurs' => [
                ['idUtilisateur' => $user->idUtilisateur, 'languePref' => 'eu']
            ]
        ]);

        $response->assertStatus(200);

        // 3. Vérification BDD
        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $enfant->idEnfant,
            'nom' => 'NomModifie'
        ]);
    }

    public function test_delete_removes_family()
    {
        $famille = Famille::factory()->create();
        $response = $this->deleteJson('/api/familles/' . $famille->idFamille);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('famille', ['idFamille' => $famille->idFamille]);
    }
}

