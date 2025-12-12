<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClasseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Création d’un utilisateur "admin" (adapté à ton projet)
        $this->admin = User::factory()->create();

        // Si tu utilises spatie/laravel-permission ou autre :
        // $this->admin->assignRole('CA');
    }

    /**
     * Helper pour passer l’auth + éventuellement le rôle CA
     */
    protected function actingAsCa()
    {
        return $this->actingAs($this->admin);
    }

    /** @test */
    public function index_displays_the_classes_list_view()
    {
        $response = $this->actingAsCa()
            ->get(route('admin.classes.index'));

        $response->assertStatus(200)
            ->assertViewIs('admin.classes.index');
    }

    /** @test */
    public function data_returns_json_for_datatables()
    {
        // Arrange
        $classe = Classe::factory()->create([
            'nom' => 'CM1 A',
            'niveau' => 'CM1',
        ]);

        // Act
        $response = $this->actingAsCa()
            ->get(route('admin.classes.data'));

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'recordsTotal',
                'recordsFiltered',
                'draw',
            ]);

        $this->assertStringContainsString('CM1 A', $response->getContent());
    }

    /** @test */
    public function show_displays_class_details_with_children()
    {
        $classe = Classe::factory()->create();
        $child1 = Enfant::factory()->create([
            'idClasse' => $classe->idClasse,
        ]);
        $child2 = Enfant::factory()->create([
            'idClasse' => $classe->idClasse,
        ]);

        $response = $this->actingAsCa()
            ->get(route('admin.classes.show', $classe));

        $response->assertStatus(200)
            ->assertViewIs('admin.classes.show')
            ->assertViewHas('classe', function ($viewClasse) use ($classe, $child1, $child2) {
                return $viewClasse->is($classe)
                    && $viewClasse->enfants->contains($child1)
                    && $viewClasse->enfants->contains($child2);
            });
    }

    /** @test */
    public function create_displays_form_with_children_without_class_and_levels()
    {
        $classe = Classe::factory()->create(['niveau' => 'CM1']);
        $childWithoutClass = Enfant::factory()->create(['idClasse' => null]);
        $childWithClass    = Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        $response = $this->actingAsCa()
            ->get(route('admin.classes.create'));

        $response->assertStatus(200)
            ->assertViewIs('admin.classes.create')
            ->assertViewHas('children')
            ->assertViewHas('levels');

        $children = $response->viewData('children');
        $this->assertTrue($children->contains($childWithoutClass));
        $this->assertFalse($children->contains($childWithClass));
    }

    /** @test */
    public function store_creates_class_and_assigns_children()
    {
        $child1 = Enfant::factory()->create(['idClasse' => null]);
        $child2 = Enfant::factory()->create(['idClasse' => null]);

        $payload = [
            'nom'      => 'CE1 A',
            'niveau'   => 'CE1',
            'children' => [$child1->idEnfant, $child2->idEnfant],
        ];

        $response = $this->actingAsCa()
            ->post(route('admin.classes.store'), $payload);

        $response->assertRedirect(route('admin.classes.index'))
            ->assertSessionHas('success', trans('classes.created_success'));

        $this->assertDatabaseHas('classe', [
            'nom'    => 'CE1 A',
            'niveau' => 'CE1',
        ]);

        $classe = Classe::where('nom', 'CE1 A')->firstOrFail();

        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $child1->idEnfant,
            'idClasse' => $classe->idClasse,
        ]);

        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $child2->idEnfant,
            'idClasse' => $classe->idClasse,
        ]);
    }

    /** @test */
    public function store_validates_required_fields_and_children_rules()
    {
        $response = $this->actingAsCa()
            ->from(route('admin.classes.create'))
            ->post(route('admin.classes.store'), []); // rien envoyé

        $response->assertRedirect(route('admin.classes.create'));
        $response->assertSessionHasErrors(['nom', 'niveau', 'children']);
    }

    /** @test */
    public function edit_displays_form_with_children_and_selected_children_ids()
    {
        $classe = Classe::factory()->create();

        $childInClass1 = Enfant::factory()->create(['idClasse' => $classe->idClasse]);
        $childInClass2 = Enfant::factory()->create(['idClasse' => $classe->idClasse]);
        $childWithoutClass = Enfant::factory()->create(['idClasse' => null]);
        $childInOtherClass = Enfant::factory()->create([
            'idClasse' => Classe::factory()->create()->idClasse,
        ]);

        $response = $this->actingAsCa()
            ->get(route('admin.classes.edit', $classe));

        $response->assertStatus(200)
            ->assertViewIs('admin.classes.edit')
            ->assertViewHasAll(['classe', 'children', 'levels', 'selectedChildrenIds']);

        $children = $response->viewData('children');
        $selected = $response->viewData('selectedChildrenIds');

        $this->assertTrue($children->contains($childWithoutClass));
        $this->assertTrue($children->contains($childInClass1));
        $this->assertFalse($children->contains($childInOtherClass));

        $this->assertContains($childInClass1->idEnfant, $selected);
        $this->assertContains($childInClass2->idEnfant, $selected);
    }

    /** @test */
    public function update_updates_class_and_syncs_children_membership()
    {
        $classe = Classe::factory()->create([
            'nom'    => 'Ancien nom',
            'niveau' => 'CE1',
        ]);

        // Enfants actuellement dans la classe
        $childStay   = Enfant::factory()->create(['idClasse' => $classe->idClasse]);
        $childLeave  = Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        // Enfant sans classe qui sera ajouté
        $childJoin   = Enfant::factory()->create(['idClasse' => null]);

        $payload = [
            'nom'      => 'Nouveau nom',
            'niveau'   => 'CE2',
            'children' => [
                $childStay->idEnfant,
                $childJoin->idEnfant,
                // childLeave n’est PAS dans la liste → doit être détaché
            ],
        ];

        $response = $this->actingAsCa()
            ->put(route('admin.classes.update', $classe), $payload);

        $response->assertRedirect(route('admin.classes.index'))
            ->assertSessionHas('success', trans('classes.updated_success'));

        // Classe mise à jour
        $this->assertDatabaseHas('classe', [
            'idClasse' => $classe->idClasse,
            'nom'      => 'Nouveau nom',
            'niveau'   => 'CE2',
        ]);

        // childStay : garde la classe
        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $childStay->idEnfant,
            'idClasse' => $classe->idClasse,
        ]);

        // childJoin : rejoint la classe
        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $childJoin->idEnfant,
            'idClasse' => $classe->idClasse,
        ]);

        // childLeave : doit être détaché
        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $childLeave->idEnfant,
            'idClasse' => null,
        ]);
    }

    /** @test */
    public function update_validates_required_fields_and_children_rules()
    {
        $classe = Classe::factory()->create();

        $response = $this->actingAsCa()
            ->from(route('admin.classes.edit', $classe))
            ->put(route('admin.classes.update', $classe), []); // rien envoyé

        $response->assertRedirect(route('admin.classes.edit', $classe));
        $response->assertSessionHasErrors(['nom', 'niveau', 'children']);
    }

    /** @test */
    public function destroy_deletes_class_and_detaches_children()
    {
        $classe = Classe::factory()->create();
        $child1 = Enfant::factory()->create(['idClasse' => $classe->idClasse]);
        $child2 = Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        $response = $this->actingAsCa()
            ->delete(route('admin.classes.destroy', $classe));

        $response->assertRedirect(route('admin.classes.index'))
            ->assertSessionHas('success', trans('classes.deleted_success'));

        $this->assertDatabaseMissing('classe', [
            'idClasse' => $classe->idClasse,
        ]);

        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $child1->idEnfant,
            'idClasse' => null,
        ]);

        $this->assertDatabaseHas('enfant', [
            'idEnfant' => $child2->idEnfant,
            'idClasse' => null,
        ]);
    }
}
