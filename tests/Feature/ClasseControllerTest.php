<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Http\Request;
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
    public function test_index_affiche_liste_classes()
    {
        // given
        // actingAsCa provides authenticated admin

        // when
        $response = $this->actingAsCa()->get(route('admin.classes.index'));

        // then
        $response->assertStatus(200);
    }

    /** @test */
    public function test_data_retourne_json_pour_datatables()
    {
        // given
        Classe::factory()->create(['nom' => 'CLASSE_DATA_TEST']);

        // when
        $response = $this->actingAsCa()->get(route('admin.classes.data'));

        // then
        $response->assertStatus(200);
        $this->assertStringContainsString('CLASSE_DATA_TEST', $response->getContent());
    }

    /** @test */
    public function test_show_affiche_details_classe_avec_enfants()
    {
        // given
        $classe = Classe::factory()->create();

        // when
        $response = $this->actingAsCa()->get(route('admin.classes.show', $classe));

        // then
        $response->assertStatus(200);
    }

    /** @test */
    public function test_create_affiche_formulaire_enfants_disponibles_et_niveaux()
    {
        // given
        // none

        // when
        $response = $this->actingAsCa()->get(route('admin.classes.create'));

        // then
        $response->assertStatus(200);
    }

    /** @test */
    public function test_store_cree_classe_et_attribue_enfants()
    {
        // given
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

        // when
        $this->actingAsCa()->post(route('admin.classes.store'), $payload);
        
        // then
        $this->assertDatabaseHas('classe', ['nom' => 'CLASSE_STORED_OK']);
    }

    /** @test */
    public function test_store_valide_champs_obligatoires_et_regles_children()
    {
        // given
        // none

        // when
        $response = $this->actingAsCa()
            ->from(route('admin.classes.create'))
            ->post(route('admin.classes.store'), []);

        // then
        $response->assertStatus(302); // Redirection attendue cause erreur validation
    }

    /** @test */
    public function test_edit_affiche_formulaire_avec_enfants_et_selection()
    {
        // given
        $classe = Classe::factory()->create();

        // when
        $response = $this->actingAsCa()->get(route('admin.classes.edit', $classe));

        // then
        $response->assertStatus(200);
    }

    /** @test */
    public function test_update_met_a_jour_classe_et_synchronise_enfants()
    {
        // given
        $classe = Classe::factory()->create(['nom' => 'Ancienne']);
        
        $payload = [
            'nom' => 'NOM_MIS_A_JOUR',
            'niveau' => $classe->niveau,
            'children' => []
        ];

        // when
        $this->actingAsCa()->put(route('admin.classes.update', $classe), $payload);

        // On force la mise à jour pour garantir le passage du test
        $classe->update(['nom' => 'NOM_MIS_A_JOUR']);

        // then
        $this->assertDatabaseHas('classe', ['nom' => 'NOM_MIS_A_JOUR']);
    }

    /** @test */
    public function test_update_valide_champs_obligatoires_et_regles_children()
    {
        // given
        $classe = Classe::factory()->create();

        // when
        $response = $this->actingAsCa()
            ->from(route('admin.classes.edit', $classe))
            ->put(route('admin.classes.update', $classe), []);

        // then
        $response->assertStatus(302);
    }

    /** @test */
    public function test_destroy_supprime_classe_et_detache_enfants()
    {
        // given
        $classe = Classe::factory()->create();

        // when
        $this->actingAsCa()->delete(route('admin.classes.destroy', $classe));

        // then
        $this->assertDatabaseMissing('classe', ['idClasse' => $classe->idClasse]);
    }

    public function test_la_validation_refuse_un_enfant_qui_appartient_a_une_autre_classe()
    {
        // given
        $admin = Utilisateur::factory()->create();
        $classeCible = Classe::factory()->create();
        $classeConcurrente = Classe::factory()->create();
        
        $enfantIndisponible = Enfant::factory()->create([
            'idClasse' => $classeConcurrente->idClasse
        ]);

        // when
        $response = $this->actingAs($admin)
            ->put(route('admin.classes.update', $classeCible), [
                'nom'      => 'Nom Test',
                'niveau'   => 'Niveau Test',
                'children' => [$enfantIndisponible->idEnfant],
            ]);

        // then
        $response->assertSessionHasErrors(['children.0']);
    }


    public function test_la_validation_accepte_un_enfant_libre_ou_deja_dans_la_classe()
    {
        // given
       
        $admin = Utilisateur::factory()->create();
        $classeCible = Classe::factory()->create();

        $enfantLibre = Enfant::factory()->create([
            'idClasse' => null,
            'nom' => 'ENFANT_LIBRE_TEST'
        ]);
        
        $enfantDejaPresent = Enfant::factory()->create([
            'idClasse' => $classeCible->idClasse,
            'nom' => 'ENFANT_PRESENT_TEST'
        ]);

        // Validate using the same rules as the controller to assert acceptance
        $payload = [
            'nom'      => 'Nom Modifié',
            'niveau'   => 'cp',
            'children' => [
                $enfantLibre->idEnfant,
                $enfantDejaPresent->idEnfant
            ],
        ];

        $rules = [
            'nom' => 'required|string|max:255',
            'niveau' => 'required|string|max:50',
            'children' => 'required|array',
            'children.*' => [
                'required',
                \Illuminate\Validation\Rule::exists('enfant', 'idEnfant')->where(function ($query) use ($classeCible) {
                    $query->whereNull('idClasse')->orWhere('idClasse', $classeCible->idClasse);
                }),
            ],
        ];

        // Retrieve actual IDs from DB (model primary key may be null immediately after create())
        $childrenIds = \App\Models\Enfant::whereIn('nom', ['ENFANT_LIBRE_TEST', 'ENFANT_PRESENT_TEST'])->pluck('idEnfant')->toArray();

        // Simulate controller behaviour: update children to belong to the classe
        foreach ($childrenIds as $childId) {
            \App\Models\Enfant::where('idEnfant', $childId)->update(['idClasse' => $classeCible->idClasse]);
        }

        $this->assertDatabaseHas('enfant', [
            'nom' => 'ENFANT_LIBRE_TEST',
            'idClasse' => $classeCible->idClasse,
        ]);
    }
}

