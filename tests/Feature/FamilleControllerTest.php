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

    public function test_api_index_returns_json_list()
    {
        // Authenticate just in case, though API route might be open or different middleware
        // api.php has 'familles' route.
        Famille::factory()->count(3)->create();

        $response = $this->getJson('/api/familles');

        $response->assertStatus(200);
        // Depending on pagination or format, assert structure
        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    public function test_web_admin_index_returns_view()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.index');
        $response->assertViewHas('familles');
    }

    public function test_api_show_returns_json_family()
    {
        $famille = Famille::factory()->create();
        $response = $this->getJson('/api/familles/' . $famille->idFamille);
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['idFamille' => $famille->idFamille]);
    }

    public function test_web_admin_show_returns_view()
    {
        $famille = Famille::factory()->create();
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.show', $famille->idFamille));

        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.show');
        $response->assertViewHas('famille');
    }

    public function test_show_returns_404_if_missing_json()
    {
        $response = $this->getJson('/api/familles/999999999');
        $response->assertStatus(404);
    }

    public function test_web_admin_show_redirects_if_missing()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.show', 99999999));

        $response->assertRedirect(route('admin.familles.index'));
    }

    public function test_web_create_returns_view()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.create');
        $response->assertViewHas(['tousUtilisateurs', 'tousEnfants']);
    }

    public function test_web_edit_returns_view()
    {
        $famille = Famille::factory()->create();
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.edit', $famille->idFamille));

        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.create'); // It reuses create view
        $response->assertViewHas('famille');
    }

    public function test_web_edit_redirects_if_missing()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.familles.edit', 999999));

        $response->assertRedirect(route('admin.familles.index'));
    }

    public function test_ajouter_creates_family_json()
    {
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

        // The route in api.php is POST /api/familles
        
        $response = $this->postJson('/api/familles', $payload);
        $response->assertStatus(201);
        
        // Handling potentially flat or nested response
        $idFamille = $response->json('famille.idFamille');
        $this->assertDatabaseHas('famille', ['idFamille' => $idFamille]);
        $this->assertDatabaseHas('enfant', ['nom' => 'Dupont', 'idFamille' => $idFamille]);
        $this->assertDatabaseHas('lier', ['idFamille' => $idFamille, 'idUtilisateur' => $user->idUtilisateur]);
    }

    public function test_delete_removes_family_as_admin()
    {
        $famille = Famille::factory()->create();
        
        // This is a web admin route: DELETE /admin/familles/{id}
        $response = $this->actingAs($this->adminUser)
                         ->delete(route('admin.familles.delete', $famille->idFamille));

        $response->assertStatus(200); // Controller returns JSON response even for web route
        $this->assertDatabaseMissing('famille', ['idFamille' => $famille->idFamille]);
    }

    public function test_delete_returns_404_if_missing()
    {
        $response = $this->actingAs($this->adminUser)
                         ->delete(route('admin.familles.delete', 999999));

        $response->assertStatus(404);
    }

    public function test_search_by_parent()
    {
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create(['nom' => 'SearchableName']);
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 100]);

        $response = $this->getJson('/api/search?q=Searchable');

        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'SearchableName']);
    }

    public function test_search_by_parent_short_query_returns_validation_error()
    {
        $response = $this->getJson('/api/search?q=a');
        $response->assertStatus(422);
    }

    public function test_search_users()
    {
        $user = Utilisateur::factory()->create(['nom' => 'UserFind']);
        
        // This route is defined in web.php and requires Auth
        $response = $this->actingAs($this->adminUser)
                         ->getJson('/api/search/users?q=UserFind');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'UserFind']);
    }
}

