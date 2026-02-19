<?php
namespace Tests\Feature;

use App\Http\Controllers\UtilisateurController;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UtilisateurControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Création des rôles nécessaires pour les tests
        Role::create(['name' => 'parent']);
        Role::create(['name' => 'admin']);

        // Crée et authentifie un utilisateur pour éviter les 401 lors des requêtes
        $this->actingUser = Utilisateur::factory()->create();
        $this->actingUser->assignRole('admin');
        $this->actingAs($this->actingUser);
    }

    /**
     * Test : Si la recherche fait moins de 2 caractères, tous les parents sont retournés.
     */
    public function test_search_without_query_returns_all_parents_ordered_by_prenom()
    {
        $parent1 = Utilisateur::factory()->create(['prenom' => 'Zoe']);
        $parent1->assignRole('parent');

        $parent2 = Utilisateur::factory()->create(['prenom' => 'Alice']);
        $parent2->assignRole('parent');

        // Requête sans paramètre 'q' (ou avec 1 seul caractère)
        // Remplace 'api.utilisateurs.search' par le nom de ta route réelle
        $response = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search'], ['q' => 'A']));

        $response->assertStatus(200);
        $json = $response->json();

        // Doit retourner les 2 parents
        $this->assertCount(2, $json);

        // Doit être trié par prénom (Alice en premier, Zoe en deuxième)
        $this->assertEquals('Alice', $json[0]['prenom']);
        $this->assertEquals('Zoe', $json[1]['prenom']);
    }

    /**
     * Test : La recherche filtre correctement par nom, prénom ou email.
     */
    public function test_search_filters_by_nom_prenom_or_email()
    {
        $parentCible = Utilisateur::factory()->create([
            'nom'    => 'Dupont',
            'prenom' => 'Jean',
            'email'  => 'jean.dupont@example.com',
        ]);
        $parentCible->assignRole('parent');

        $parentAutre = Utilisateur::factory()->create([
            'nom'    => 'Martin',
            'prenom' => 'Paul',
            'email'  => 'paul@example.com',
        ]);
        $parentAutre->assignRole('parent');

        // Test de recherche par Nom
        $responseNom = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search'], ['q' => 'Dupont']));
        $responseNom->assertStatus(200)->assertJsonCount(1);
        $this->assertEquals($parentCible->idUtilisateur, $responseNom->json()[0]['idUtilisateur']);

        // Test de recherche par Prénom
        $responsePrenom = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search'], ['q' => 'Jean']));
        $responsePrenom->assertStatus(200)->assertJsonCount(1);

        // Test de recherche par Email partiel
        $responseEmail = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search'], ['q' => 'dupont@ex']));
        $responseEmail->assertStatus(200)->assertJsonCount(1);
    }

    /**
     * Test : La recherche exclut strictement les utilisateurs qui ne sont pas des 'parents'.
     */
    public function test_search_strictly_excludes_non_parents()
    {
        // Création d'un parent
        $parent = Utilisateur::factory()->create(['nom' => 'Dubois', 'prenom' => 'Marie']);
        $parent->assignRole('parent');

        // Création d'un admin avec le MÊME nom
        $admin = Utilisateur::factory()->create(['nom' => 'Dubois', 'prenom' => 'Luc']);
        $admin->assignRole('admin');

        $response = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search'], ['q' => 'Dubois']));

        $response->assertStatus(200);
        $json = $response->json();

        // Seul le parent doit être retourné
        $this->assertCount(1, $json);
        $this->assertEquals('Marie', $json[0]['prenom']);
    }

    /**
     * Test : Format de retour correct (uniquement les champs demandés).
     */
    public function test_search_returns_only_specific_columns()
    {
        $parent = Utilisateur::factory()->create();
        $parent->assignRole('parent');

        $response = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search']));

        $response->assertStatus(200);

        // Vérifie la structure exacte du JSON retourné
        $response->assertJsonStructure([
            '*' => [
                'idUtilisateur',
                'nom',
                'prenom',
                'email',
            ],
        ]);

        // S'assure que d'autres champs sensibles (comme 'password' ou 'created_at') ne fuitent pas
        $this->assertArrayNotHasKey('password', $response->json()[0]);
    }

    /**
     * Test : Recherche qui ne donne aucun résultat.
     */
    public function test_search_returns_empty_array_if_no_match()
    {
        $parent = Utilisateur::factory()->create(['nom' => 'Martin']);
        $parent->assignRole('parent');

        $response = $this->getJson(action([\App\Http\Controllers\UtilisateurController::class, 'search'], ['q' => 'Inconnu']));

        $response->assertStatus(200);

        // S'assure que la réponse est un tableau vide
        $this->assertEquals([], $response->json());
    }

    public function test_classe_possede_methode_searchByNom(): void
    {
        // given
        // no special setup needed

        // when
        $exists = method_exists(\App\Http\Controllers\UtilisateurController::class, 'searchByNom');

        // then
        $this->assertTrue($exists);
    }

    public function test_searchByNom_sans_nom_retourne_400(): void
    {
        // given
        $controller = new UtilisateurController();
        $request    = Request::create('/search', 'GET');

        // when
        $response = $controller->searchByNom($request);

        // then
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_searchByNom_sans_resultats_retourne_404(): void
    {
        // given
        // Use a very unlikely name to avoid needing mocks
        $name = 'no_such_name_' . uniqid();

        $controller = new UtilisateurController();
        $request    = Request::create('/search', 'GET', ['nom' => $name]);

        // when
        $response = $controller->searchByNom($request);

        // then
        $this->assertEquals(404, $response->getStatusCode());
    }
}
