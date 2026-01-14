<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;
use App\Models\Utilisateur;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Role;
use Illuminate\Support\Facades\Config;

class FamilleControllerTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure CA role exists
        if (Role::where('name', 'CA')->count() == 0) {
            Role::create(['name' => 'CA']);
        }

        $this->adminUser = Utilisateur::factory()->create();
        $this->adminUser->roles()->attach(Role::where('name', 'CA')->first());
    }

    public function test_api_index_retourne_liste_json()
    {
        // given
        Famille::factory()->count(3)->create();

        // when
        $response = $this->getJson('/api/familles');

        // then
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    public function test_web_admin_index_retourne_vue()
    {
        // given
        // admin user prepared in setUp

        // when
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.index'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.index');
        $response->assertViewHas('familles');
    }

    public function test_api_show_retourne_famille_json()
    {
        // given
        $famille = Famille::factory()->create();

        // when
        $response = $this->getJson('/api/familles/' . $famille->idFamille);
        
        // then
        $response->assertStatus(200)
                 ->assertJsonFragment(['idFamille' => $famille->idFamille]);
    }

    public function test_web_admin_show_retourne_vue()
    {
        // given
        $famille = Famille::factory()->create();
        
        // when
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.show', $famille->idFamille));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.show');
        $response->assertViewHas('famille');
    }

    public function test_show_retourne_404_si_json_manquant()
    {
        // when
        $response = $this->getJson('/api/familles/999999999');

        // then
        $response->assertStatus(404);
    }

    public function test_web_admin_show_redirects_if_missing()
    {
        // when
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.show', 99999999));

        // then
        $response->assertRedirect(route('admin.familles.index'));
    }

    public function test_web_create_retourne_vue()
    {
        // when
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.create'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.create');
        $response->assertViewHas(['tousUtilisateurs', 'tousEnfants']);
    }

    public function test_web_edit_retourne_vue()
    {
        // given
        $famille = Famille::factory()->create();
        
        // when
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.edit', $famille->idFamille));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.create'); // It reuses create view
        $response->assertViewHas('famille');
    }

    public function test_web_edit_redirige_si_manquant()
    {
        // when
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.edit', 999999));

        // then
        $response->assertRedirect(route('admin.familles.index'));
    }

    public function test_ajouter_creates_family_json()
    {
        // given
        $classe = Classe::factory()->create();
        $user = Utilisateur::factory()->create();

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

        // when
        $response = $this->postJson('/api/familles', $payload);

        // then
        $response->assertStatus(201);
        
        $idFamille = $response->json('famille.idFamille');
        $this->assertDatabaseHas('famille', ['idFamille' => $idFamille]);
        $this->assertDatabaseHas('enfant', ['nom' => 'Dupont', 'idFamille' => $idFamille]);
        $this->assertDatabaseHas('lier', ['idFamille' => $idFamille, 'idUtilisateur' => $user->idUtilisateur]);
    }

    public function test_delete_removes_family_as_admin()
    {
        // given
        $famille = Famille::factory()->create();
        
        // when
        $response = $this->actingAs($this->adminUser)
                         ->delete(route('admin.familles.delete', $famille->idFamille));

        // then
        $response->assertStatus(200);
        $this->assertDatabaseMissing('famille', ['idFamille' => $famille->idFamille]);
    }

    public function test_suppression_retourne_404_si_introuvable()
    {
        // when
        $response = $this->actingAs($this->adminUser)
                         ->delete(route('admin.familles.delete', 999999));

        // then
        $response->assertStatus(404);
    }

    public function test_search_by_parent()
    {
        // given
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create(['nom' => 'SearchableName']);
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 100]);

        // when
        $response = $this->getJson('/api/search?q=Searchable');

        // then
        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'SearchableName']);
    }

    public function test_recherche_par_parent_requete_courte_retourne_erreur_validation()
    {
        // when
        $response = $this->getJson('/api/search?q=a');

        // then
        $response->assertStatus(422);
    }

    public function test_search_users()
    {
        // given
        $user = Utilisateur::factory()->create(['nom' => 'UserFind']);
        
        // This route is defined in web.php and requires Auth
        // when
        $response = $this->actingAs($this->adminUser)
                         ->getJson('/api/search/users?q=UserFind');
        
        // then
        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'UserFind']);
    }
}

