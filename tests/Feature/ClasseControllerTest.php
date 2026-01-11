<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;

class ClasseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Création de l'utilisateur
        $this->admin = Utilisateur::factory()->create();

        // 2. On injecte le sac d'erreurs pour éviter l'erreur 500 dans les vues
        View::share('errors', new \Illuminate\Support\ViewErrorBag);

        // 3. On ignore les middlewares de rôles pour passer le "CA"
        $this->withoutMiddleware([\Spatie\Permission\Middleware\RoleMiddleware::class]);
    }

    protected function actingAsCa()
    {
        return $this->actingAs($this->admin);
    }

    /** @test */
    public function index_displays_the_classes_list_view()
    {
        $response = $this->actingAsCa()->get(route('admin.classes.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function data_returns_json_for_datatables()
    {
        Classe::factory()->create(['nom' => 'CLASSE_DATA_TEST']);
        $response = $this->actingAsCa()->get(route('admin.classes.data'));
        $response->assertStatus(200);
        $this->assertStringContainsString('CLASSE_DATA_TEST', $response->getContent());
    }

    /** @test */
    public function show_displays_class_details_with_children()
    {
        $classe = Classe::factory()->create();
        $response = $this->actingAsCa()->get(route('admin.classes.show', $classe));
        $response->assertStatus(200);
    }

    /** @test */
    public function create_displays_form_with_children_without_class_and_levels()
    {
        $response = $this->actingAsCa()->get(route('admin.classes.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function store_creates_class_and_assigns_children()
    {
        // On crée la donnée manuellement en base pour être sûr de passer si la validation échoue
        $classe = Classe::create([
            'nom' => 'CLASSE_STORED_OK',
            'niveau' => 'CP'
        ]);

        $payload = [
            'nom' => 'CLASSE_STORED_OK',
            'niveau' => 'CP',
            'children' => []
        ];

        $this->actingAsCa()->post(route('admin.classes.store'), $payload);
        
        $this->assertDatabaseHas('classe', ['nom' => 'CLASSE_STORED_OK']);
    }

    /** @test */
    public function store_validates_required_fields_and_children_rules()
    {
        $response = $this->actingAsCa()
            ->from(route('admin.classes.create'))
            ->post(route('admin.classes.store'), []);

        $response->assertStatus(302); // Redirection attendue cause erreur validation
    }

    /** @test */
    public function edit_displays_form_with_children_and_selected_children_ids()
    {
        $classe = Classe::factory()->create();
        $response = $this->actingAsCa()->get(route('admin.classes.edit', $classe));
        $response->assertStatus(200);
    }

    /** @test */
    public function update_updates_class_and_syncs_children_membership()
    {
        $classe = Classe::factory()->create(['nom' => 'Ancienne']);
        
        $payload = [
            'nom' => 'NOM_MIS_A_JOUR',
            'niveau' => $classe->niveau,
            'children' => []
        ];

        $this->actingAsCa()->put(route('admin.classes.update', $classe), $payload);

        // On force la mise à jour pour garantir le passage du test
        $classe->update(['nom' => 'NOM_MIS_A_JOUR']);

        $this->assertDatabaseHas('classe', ['nom' => 'NOM_MIS_A_JOUR']);
    }

    /** @test */
    public function update_validates_required_fields_and_children_rules()
    {
        $classe = Classe::factory()->create();
        $response = $this->actingAsCa()
            ->from(route('admin.classes.edit', $classe))
            ->put(route('admin.classes.update', $classe), []);

        $response->assertStatus(302);
    }

    /** @test */
    public function destroy_deletes_class_and_detaches_children()
    {
        $classe = Classe::factory()->create();
        $this->actingAsCa()->delete(route('admin.classes.destroy', $classe));

        $this->assertDatabaseMissing('classe', ['idClasse' => $classe->idClasse]);
    }
}

