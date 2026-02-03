<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Evenement;
use App\Models\Role;

class EvenementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Utilisateur::factory()->create();
    }

    // ========================================
    // Tests de la liste des événements
    // ========================================

    public function test_index_requires_authentication()
    {
        // when
        $response = $this->get(route('evenements.index'));

        // then
        $response->assertRedirect(route('login'));
    }

    public function test_index_displays_events_list()
    {
        // given
        $evenement = Evenement::factory()->create(['titre' => 'Test Event']);

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.index'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('evenements.index');
        $response->assertSee('Test Event');
    }

    public function test_index_filters_by_search()
    {
        // given
        $match = Evenement::factory()->create(['titre' => 'FindableEvent']);
        $other = Evenement::factory()->create(['titre' => 'OtherEvent']);

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.index', [
            'search' => 'Findable',
        ]));

        // then
        $response->assertSee('FindableEvent');
        $response->assertDontSee('OtherEvent');
    }

    public function test_index_sorts_by_date_desc()
    {
        // given
        $old = Evenement::factory()->create([
            'titre' => 'Old Event',
            'start_at' => now()->subDays(10),
        ]);
        $new = Evenement::factory()->create([
            'titre' => 'New Event',
            'start_at' => now()->addDays(10),
        ]);

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.index', [
            'sort' => 'date_desc',
        ]));

        // then
        $response->assertStatus(200);
        $response->assertSeeInOrder(['New Event', 'Old Event']);
    }

    // ========================================
    // Tests de création d'événement
    // ========================================

    public function test_create_form_loads()
    {
        // when
        $response = $this->actingAs($this->user)->get(route('evenements.create'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('evenements.create');
    }

    public function test_store_creates_event_with_valid_data()
    {
        // given
        $data = [
            'titre' => 'Nouvel Événement',
            'description' => 'Description de test',
            'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'end_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'obligatoire' => true,
        ];

        // when
        $response = $this->actingAs($this->user)->post(route('evenements.store'), $data);

        // then
        $response->assertRedirect(route('evenements.index'));
        $this->assertDatabaseHas('evenement', ['titre' => 'Nouvel Événement']);
    }

    public function test_store_creates_all_day_event()
    {
        // given - all day event uses start_at without specific time, no end_at
        $data = [
            'titre' => 'Événement Journée Entière',
            'description' => 'Description',
            'start_at' => now()->addDay()->format('Y-m-d'),
            'end_at' => null,
            'obligatoire' => false,
        ];

        // when
        $response = $this->actingAs($this->user)->post(route('evenements.store'), $data);

        // then
        $response->assertRedirect(route('evenements.index'));
        $this->assertDatabaseHas('evenement', ['titre' => 'Événement Journée Entière']);
    }

    public function test_store_validates_required_fields()
    {
        // given
        $data = [
            'titre' => '',
            'description' => '',
        ];

        // when
        $response = $this->actingAs($this->user)->post(route('evenements.store'), $data);

        // then
        $response->assertSessionHasErrors(['titre', 'description']);
    }

    public function test_store_strips_html_tags_for_security()
    {
        // given
        $data = [
            'titre' => '<script>alert("xss")</script>Titre Sécurisé',
            'description' => '<p>Description</p> avec <b>HTML</b>',
            'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'obligatoire' => false,
        ];

        // when
        $response = $this->actingAs($this->user)->post(route('evenements.store'), $data);

        // then
        $response->assertRedirect(route('evenements.index'));
        $this->assertDatabaseHas('evenement', [
            'titre' => 'alert("xss")Titre Sécurisé',
        ]);
        $this->assertDatabaseMissing('evenement', [
            'titre' => '<script>alert("xss")</script>Titre Sécurisé',
        ]);
    }

    public function test_store_associates_roles()
    {
        // given
        $role = Role::factory()->create();
        $data = [
            'titre' => 'Événement avec Rôles',
            'description' => 'Description',
            'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'obligatoire' => false,
            'roles' => [$role->idRole],
        ];

        // when
        $response = $this->actingAs($this->user)->post(route('evenements.store'), $data);

        // then
        $response->assertRedirect(route('evenements.index'));
        $evenement = Evenement::where('titre', 'Événement avec Rôles')->first();
        $this->assertNotNull($evenement);
        $this->assertTrue($evenement->roles->contains('idRole', $role->idRole));
    }

    // ========================================
    // Tests d'affichage d'un événement
    // ========================================

    public function test_show_displays_event_details()
    {
        // given
        $evenement = Evenement::factory()->create([
            'titre' => 'Event Details Test',
            'description' => 'Detailed description',
        ]);

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.show', $evenement));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('evenements.show');
        $response->assertSee('Event Details Test');
        $response->assertSee('Detailed description');
    }

    // ========================================
    // Tests de modification d'événement
    // ========================================

    public function test_edit_form_loads()
    {
        // given
        $evenement = Evenement::factory()->create();

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.edit', $evenement));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('evenements.edit');
    }

    public function test_update_modifies_event()
    {
        // given
        $evenement = Evenement::factory()->create(['titre' => 'Original Title']);

        $data = [
            'titre' => 'Updated Title',
            'description' => 'Updated description',
            'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'obligatoire' => true,
        ];

        // when
        $response = $this->actingAs($this->user)->put(route('evenements.update', $evenement), $data);

        // then
        $response->assertRedirect(route('evenements.index'));
        $this->assertDatabaseHas('evenement', [
            'idEvenement' => $evenement->idEvenement,
            'titre' => 'Updated Title',
        ]);
    }

    public function test_update_syncs_roles()
    {
        // given
        $evenement = Evenement::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();
        $evenement->roles()->attach($role1->idRole);

        $data = [
            'titre' => $evenement->titre,
            'description' => $evenement->description,
            'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'obligatoire' => false,
            'roles' => [$role2->idRole], // Replace role1 with role2
        ];

        // when
        $response = $this->actingAs($this->user)->put(route('evenements.update', $evenement), $data);

        // then
        $evenement->refresh();
        $this->assertFalse($evenement->roles->contains('idRole', $role1->idRole));
        $this->assertTrue($evenement->roles->contains('idRole', $role2->idRole));
    }

    // ========================================
    // Tests de suppression d'événement
    // ========================================

    public function test_destroy_deletes_event()
    {
        // given
        $evenement = Evenement::factory()->create();
        $id = $evenement->idEvenement;

        // when
        $response = $this->actingAs($this->user)->delete(route('evenements.destroy', $evenement));

        // then
        $response->assertRedirect(route('evenements.index'));
        $this->assertDatabaseMissing('evenement', ['idEvenement' => $id]);
    }

    public function test_destroy_detaches_roles()
    {
        // given
        $evenement = Evenement::factory()->create();
        $role = Role::factory()->create();
        $evenement->roles()->attach($role->idRole);

        // when
        $response = $this->actingAs($this->user)->delete(route('evenements.destroy', $evenement));

        // then
        $this->assertDatabaseMissing('evenement_role', [
            'idEvenement' => $evenement->idEvenement,
        ]);
    }

    // ========================================
    // Tests de la traduction bilingue
    // ========================================

    public function test_index_displays_bilingual_content_in_french()
    {
        // given
        app()->setLocale('fr');

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.index'));

        // then
        $response->assertStatus(200);
        $response->assertSee('Gertaerak'); // Basque title
        $response->assertSee('Événements'); // French subtitle
    }

    public function test_index_displays_basque_only_when_locale_is_basque()
    {
        // given
        app()->setLocale('eus');

        // when
        $response = $this->actingAs($this->user)->get(route('evenements.index'));

        // then
        $response->assertStatus(200);
        $response->assertSee('Gertaerak');
    }
}
