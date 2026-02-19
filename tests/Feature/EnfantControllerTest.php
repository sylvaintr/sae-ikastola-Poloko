<?php
namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Famille;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnfantControllerTest extends TestCase
{
    use RefreshDatabase; // Réinitialise la BDD à chaque test
    use WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Création d'un utilisateur admin pour l'authentification
        // Adaptez selon votre système d'authentification (ex: Spatie Roles)
        $this->admin = Utilisateur::factory()->create();
        // Ensure CA role exists and assign to admin so admin routes are accessible in tests
        $role = Role::firstOrCreate(['name' => 'CA'], ['guard_name' => 'web']);
        $this->admin->roles()->attach($role->idRole);
    }

    /**
     * TEST: Index (Liste)
     */
    public function test_index_displays_enfants_list()
    {
        Enfant::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.enfants.index');
        $response->assertViewHas('enfants');
    }

    public function test_index_can_search_enfant()
    {
        $enfantCible = Enfant::factory()->create(['nom' => 'FindMe']);
        $enfantAutre = Enfant::factory()->create(['nom' => 'Hidden']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.index', ['search' => 'FindMe']));

        $response->assertStatus(200);
        $response->assertSee('FindMe');
        $response->assertDontSee('Hidden');
    }

    public function test_index_can_sort_by_classe()
    {
        // Test spécifique pour le bloc if ($sortColumn === 'classe')
        $classeA = Classe::factory()->create(['nom' => 'A-Classe']);
        $classeB = Classe::factory()->create(['nom' => 'B-Classe']);

        Enfant::factory()->create(['idClasse' => $classeB->idClasse]);
        Enfant::factory()->create(['idClasse' => $classeA->idClasse]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.index', ['sort' => 'classe', 'direction' => 'asc']));

        $response->assertStatus(200);
        // On vérifie juste que la requête ne plante pas (SQL join correct)
        $response->assertViewHas('enfants');
    }

    /**
     * TEST: Create (Formulaire)
     */
    public function test_create_displays_form_with_data()
    {
        Classe::factory()->count(2)->create();
        Famille::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.enfants.create');
        $response->assertViewHas(['classes', 'familles']);
    }

    /**
     * TEST: Store (Enregistrement)
     */
    public function test_store_creates_enfant_successfully()
    {
        $classe  = Classe::factory()->create();
        $famille = Famille::factory()->create();

        $data = [
            'nom'            => 'Dupont',
            'prenom'         => 'Jean',
            'dateN'          => '2015-05-10',
            'sexe'           => 'M',
            'NNI'            => '1234567890', // String de 10 chiffres
            'nbFoisGarderie' => 5,
            'idClasse'       => $classe->idClasse,
            'idFamille'      => $famille->idFamille,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.enfants.store'), $data);

        $response->assertRedirect(route('admin.enfants.index'));
        $response->assertSessionHas('success');

        // Vérification en base de données
        $this->assertDatabaseHas('enfant', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'NNI'      => 1234567890, // Vérifie que le cast (int) a fonctionné
            'idClasse' => $classe->idClasse,
        ]);
    }

    public function test_store_fails_validation_invalid_nni()
    {
        $data = [
            'nom'    => 'Test',
            'prenom' => 'Jean',
            'dateN'  => '2015-01-01',
            'sexe'   => 'M',
            'NNI'    => '123A567890', // Invalide (contient une lettre)
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.enfants.store'), $data);

        $response->assertSessionHasErrors(['NNI']);
    }

    /**
     * TEST: Show (Détails)
     */
    public function test_show_displays_enfant_details()
    {
        $enfant = Enfant::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.show', $enfant->idEnfant));

        $response->assertStatus(200);
        $response->assertViewIs('admin.enfants.show');
        $response->assertSee($enfant->nom);
    }

    public function test_show_redirects_if_enfant_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.show', 99999)); // ID inexistant

        $response->assertRedirect(route('admin.enfants.index'));
    }

    /**
     * TEST: Edit & Update
     */
    public function test_update_modifies_enfant_successfully()
    {
        $enfant = Enfant::factory()->create([
            'nom' => 'AncienNom',
            'NNI' => 1111111111,
        ]);

        $data = [
            'nom'            => 'NouveauNom',
            'prenom'         => $enfant->prenom,
            'dateN'          => $enfant->dateN->format('Y-m-d'), // Assurez-vous du format date
            'sexe'           => $enfant->sexe,
            'NNI'            => '2222222222', // String valide
            'nbFoisGarderie' => 0,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.enfants.update', $enfant->idEnfant), $data);

        $response->assertRedirect(route('admin.enfants.index'));

        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $enfant->idEnfant,
            'nom'      => 'NouveauNom',
            'NNI'      => 2222222222, // Vérification du cast entier
        ]);
    }

    public function test_update_redirects_if_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.enfants.update', 99999), []);

        $response->assertRedirect(route('admin.enfants.index'));
    }

    /**
     * TEST: Destroy (Suppression HTML & JSON)
     */
    public function test_destroy_deletes_enfant_via_html_request()
    {
        $enfant = Enfant::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.enfants.destroy', $enfant->idEnfant));

        $response->assertRedirect(route('admin.enfants.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('enfant', ['idEnfant' => $enfant->idEnfant]);
    }

    public function test_destroy_deletes_enfant_via_json_request()
    {
        $enfant = Enfant::factory()->create();

        // On simule une requête AJAX/API qui demande du JSON
        $response = $this->actingAs($this->admin)
            ->json('DELETE', route('admin.enfants.destroy', $enfant->idEnfant));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Enfant supprimé avec succès']);
        $this->assertDatabaseMissing('enfant', ['idEnfant' => $enfant->idEnfant]);
    }

    public function test_destroy_returns_404_json_if_enfant_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->json('DELETE', route('admin.enfants.destroy', 99999));

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Enfant non trouvé']);
    }
    /** @test */
    public function test_edit_displays_form_with_correct_data()
    {
        $enfant = Enfant::factory()->create();
        // On crée quelques classes et familles pour vérifier qu'elles sont passées à la vue
        Classe::factory()->count(2)->create();
        Famille::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.edit', $enfant->idEnfant));

        $response->assertStatus(200);
        $response->assertViewIs('admin.enfants.edit');
        $response->assertViewHas('enfant', function ($viewEnfant) use ($enfant) {
            return $viewEnfant->idEnfant === $enfant->idEnfant;
        });
        $response->assertViewHas(['classes', 'familles']);
    }

/** @test */
    public function test_edit_redirects_if_enfant_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.edit', 999999)); // ID inexistant

        $response->assertRedirect(route('admin.enfants.index'));
    }
/** @test */
    public function test_index_handles_invalid_sort_parameters_fallback()
    {
        Enfant::factory()->count(3)->create();

        // On envoie n'importe quoi dans 'sort' et 'direction'
        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.index', [
                'sort'      => 'colonne_qui_n_existe_pas', // Doit devenir 'nom'
                'direction' => 'zigzag',                   // Doit devenir 'asc'
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('sortColumn', 'nom');    // Vérifie le fallback
        $response->assertViewHas('sortDirection', 'asc'); // Vérifie le fallback
    }
/** @test */
    public function test_index_can_sort_by_famille_id()
    {
        // Famille A (ID petit)
        $famille1 = Famille::factory()->create(['idFamille' => 10]);
        // Famille B (ID grand)
        $famille2 = Famille::factory()->create(['idFamille' => 20]);

        // Enfant Z (dans famille 1) -> Devrait être premier si on trie par Famille ASC
        $enfantZ = Enfant::factory()->create(['nom' => 'Zorro', 'idFamille' => $famille1->idFamille]);

        // Enfant A (dans famille 2) -> Devrait être deuxième si on trie par Famille ASC
        $enfantA = Enfant::factory()->create(['nom' => 'Albert', 'idFamille' => $famille2->idFamille]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enfants.index', [
                'sort'      => 'famille',
                'direction' => 'asc',
            ]));

        $response->assertStatus(200);

        // On récupère les enfants envoyés à la vue
        $enfantsView = $response->original->getData()['enfants'];

        // Vérification : le premier de la liste doit être Zorro (car idFamille 10 < 20)
        $this->assertEquals($enfantZ->idEnfant, $enfantsView->first()->idEnfant);
        $this->assertEquals($enfantA->idEnfant, $enfantsView->last()->idEnfant);
    }
/** @test */
    public function test_store_handles_empty_relationships_by_setting_null()
    {
        $data = [
            'nom'            => 'Solo',
            'prenom'         => 'Han',
            'dateN'          => '2010-01-01',
            'sexe'           => 'M',
            'NNI'            => '1234567899',
            'nbFoisGarderie' => 0,
            // On simule l'envoi de champs vides (comme un formulaire HTML vide)
            'idClasse'       => '',
            'idFamille'      => null,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.enfants.store'), $data);

        $response->assertRedirect(route('admin.enfants.index'));

        // Vérification cruciale : en base, cela doit être NULL
        $this->assertDatabaseHas('enfant', [
            'nom'       => 'Solo',
            'idClasse'  => null,
            'idFamille' => null,
        ]);
    }
/** @test */
    public function test_destroy_redirects_html_request_if_not_found()
    {
        // On appelle DELETE sans demander de JSON (Header standard)
        // sur un ID qui n'existe pas
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.enfants.destroy', 999999));

        // Doit rediriger vers l'index (comportement du handleNotFoundResponse en HTML)
        $response->assertRedirect(route('admin.enfants.index'));
    }
}
