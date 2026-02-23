<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Evenement;
use App\Models\Tache;
use App\Models\Role;

class CalendrierControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Utilisateur::factory()->create();
    }

    // ========================================
    // Tests de la page calendrier
    // ========================================

    public function test_calendar_page_requires_authentication()
    {
        // when
        $response = $this->get(route('calendrier.index'));

        // then
        $response->assertRedirect(route('login'));
    }

    public function test_calendar_page_loads_for_authenticated_user()
    {
        // when
        $response = $this->actingAs($this->user)->get(route('calendrier.index'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('calendrier.index');
    }

    public function test_calendar_page_contains_calendar_root_element()
    {
        // when
        $response = $this->actingAs($this->user)->get(route('calendrier.index'));

        // then
        $response->assertSee('id="calendar-root"', false);
    }

    public function test_calendar_page_contains_sync_modal()
    {
        // when
        $response = $this->actingAs($this->user)->get(route('calendrier.index'));

        // then
        $response->assertSee('id="syncCalendarModal"', false);
    }

    // ========================================
    // Tests de l'API events
    // ========================================

    public function test_events_endpoint_returns_json()
    {
        // given
        $this->actingAs($this->user);

        // when
        $response = $this->getJson(route('calendrier.events'));

        // then
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_events_endpoint_returns_evenements()
    {
        // given
        $this->actingAs($this->user);
        $evenement = Evenement::factory()->create([
            'titre' => 'Test Event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Test Event']);
    }

    public function test_events_endpoint_includes_demandes_non_terminees()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Demande Test',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Demande Test']);
    }

    public function test_events_endpoint_excludes_demandes_terminees()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Demande Terminée',
            'etat' => 'Terminé',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => 'Demande Terminée']);
    }

    public function test_events_endpoint_filters_by_date_range()
    {
        // given
        $this->actingAs($this->user);

        $inRange = Evenement::factory()->create([
            'titre' => 'In Range Event',
            'start_at' => now()->addDays(5),
            'end_at' => null, // Explicitly no end date
        ]);

        $outOfRange = Evenement::factory()->create([
            'titre' => 'Out Of Range Event',
            'start_at' => now()->addMonths(3),
            'end_at' => null, // Explicitly no end date
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'In Range Event']);
        $response->assertJsonMissing(['title' => 'Out Of Range Event']);
    }

    public function test_events_have_correct_structure_for_evenement()
    {
        // given
        $this->actingAs($this->user);
        $evenement = Evenement::factory()->create([
            'titre' => 'Structured Event',
            'description' => 'Test description',
            'obligatoire' => true,
            'start_at' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertStatus(200);
        $json = $response->json();

        $event = collect($json)->firstWhere('title', 'Structured Event');
        $this->assertNotNull($event);
        $this->assertStringStartsWith('event-', $event['id']);
        $this->assertEquals('evenement', $event['extendedProps']['type']);
        $this->assertEquals('Test description', $event['extendedProps']['description']);
        $this->assertTrue($event['extendedProps']['obligatoire']);
    }

    public function test_events_have_correct_structure_for_demande()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Structured Demande',
            'description' => 'Demande description',
            'urgence' => 'Élevée',
            'etat' => 'En cours',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertStatus(200);
        $json = $response->json();

        $event = collect($json)->firstWhere('title', 'Structured Demande');
        $this->assertNotNull($event);
        $this->assertStringStartsWith('demande-', $event['id']);
        $this->assertEquals('demande', $event['extendedProps']['type']);
        $this->assertEquals('Demande description', $event['extendedProps']['description']);
        $this->assertEquals('Élevée', $event['extendedProps']['urgence']);
        $this->assertEquals('En cours', $event['extendedProps']['etat']);
        $this->assertTrue($event['allDay']);
    }

    // ========================================
    // Tests de la locale du calendrier
    // ========================================

    public function test_calendar_page_has_french_locale_when_language_is_french()
    {
        // given
        $this->user->languePref = 'fr';
        $this->user->save();

        // when
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'fr'])
            ->get(route('calendrier.index'));

        // then
        $response->assertStatus(200);
        $response->assertSee('data-locale="fr"', false);
    }

    public function test_calendar_page_has_basque_locale_when_language_is_basque()
    {
        // given
        app()->setLocale('eus');

        // when
        $response = $this->actingAs($this->user)->get(route('calendrier.index'));

        // then
        $response->assertStatus(200);
        $response->assertSee('data-locale="eu"', false);
    }

    // ========================================
    // Tests du filtrage par rôles
    // ========================================

    public function test_user_sees_events_with_matching_roles()
    {
        // given
        $role = Role::factory()->create();
        $this->user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $matchingEvent = Evenement::factory()->create([
            'titre' => 'Matching Role Event',
            'start_at' => now()->addDay(),
        ]);
        $matchingEvent->roles()->attach($role->idRole);

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Matching Role Event']);
    }

    public function test_user_does_not_see_events_with_different_roles()
    {
        // given
        $userRole = Role::factory()->create(['name' => 'user_role']);
        $otherRole = Role::factory()->create(['name' => 'other_role']);
        $this->user->rolesCustom()->attach($userRole->idRole, ['model_type' => Utilisateur::class]);

        $differentRoleEvent = Evenement::factory()->create([
            'titre' => 'Different Role Event',
            'start_at' => now()->addDay(),
        ]);
        $differentRoleEvent->roles()->attach($otherRole->idRole);

        // when - use actingAsNonAdmin to test role filtering without admin bypass
        $response = $this->actingAsNonAdmin($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonMissing(['title' => 'Different Role Event']);
    }

    public function test_user_sees_events_without_any_roles()
    {
        // given
        $role = Role::factory()->create();
        $this->user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $noRoleEvent = Evenement::factory()->create([
            'titre' => 'No Role Event',
            'start_at' => now()->addDay(),
        ]);
        // Event without any roles attached

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'No Role Event']);
    }

    public function test_user_without_roles_only_sees_events_without_roles()
    {
        // given - user has no roles (default from factory)
        $role = Role::factory()->create();

        $eventWithRole = Evenement::factory()->create([
            'titre' => 'Event With Role',
            'start_at' => now()->addDay(),
        ]);
        $eventWithRole->roles()->attach($role->idRole);

        $eventWithoutRole = Evenement::factory()->create([
            'titre' => 'Event Without Role',
            'start_at' => now()->addDay(),
        ]);

        // when - use actingAsNonAdmin to test role filtering without admin bypass
        $response = $this->actingAsNonAdmin($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonMissing(['title' => 'Event With Role']);
        $response->assertJsonFragment(['title' => 'Event Without Role']);
    }

    public function test_user_sees_demandes_linked_to_event_with_matching_role()
    {
        // given
        $role = Role::factory()->create();
        $this->user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $event = Evenement::factory()->create(['start_at' => now()->addDay()]);
        $event->roles()->attach($role->idRole);

        $demande = Tache::factory()->create([
            'titre' => 'Visible Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'idEvenement' => $event->idEvenement,
        ]);

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Visible Demande']);
    }

    public function test_user_does_not_see_demandes_linked_to_event_with_different_role()
    {
        // given
        $userRole = Role::factory()->create(['name' => 'user_role_demande']);
        $otherRole = Role::factory()->create(['name' => 'other_role_demande']);
        $this->user->rolesCustom()->attach($userRole->idRole, ['model_type' => Utilisateur::class]);

        $event = Evenement::factory()->create(['start_at' => now()->addDay()]);
        $event->roles()->attach($otherRole->idRole);

        $demande = Tache::factory()->create([
            'titre' => 'Hidden Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'idEvenement' => $event->idEvenement,
        ]);

        // when - use actingAsNonAdmin to test role filtering without admin bypass
        $response = $this->actingAsNonAdmin($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonMissing(['title' => 'Hidden Demande']);
    }

    public function test_user_sees_demandes_without_linked_event()
    {
        // given
        $role = Role::factory()->create();
        $this->user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $demande = Tache::factory()->create([
            'titre' => 'Orphan Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'idEvenement' => null,
        ]);

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Orphan Demande']);
    }

    public function test_user_sees_demandes_linked_to_event_without_roles()
    {
        // given
        $role = Role::factory()->create();
        $this->user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $event = Evenement::factory()->create(['start_at' => now()->addDay()]);
        // Event without roles

        $demande = Tache::factory()->create([
            'titre' => 'Public Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'idEvenement' => $event->idEvenement,
        ]);

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Public Demande']);
    }

    // ========================================
    // Tests du rôle administrateur (CA)
    // ========================================

    public function test_ca_admin_sees_all_events_regardless_of_roles()
    {
        // given - Create CA role and assign to user
        $caRole = Role::firstOrCreate(['name' => 'CA', 'guard_name' => 'web']);
        $otherRole = Role::factory()->create(['name' => 'other_role_ca']);

        $this->user->assignRole('CA');

        // Event with different role (not CA)
        $restrictedEvent = Evenement::factory()->create([
            'titre' => 'Restricted Event',
            'start_at' => now()->addDay(),
        ]);
        $restrictedEvent->roles()->attach($otherRole->idRole);

        // Event without any roles
        $publicEvent = Evenement::factory()->create([
            'titre' => 'Public Event CA',
            'start_at' => now()->addDay(),
        ]);

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then - CA should see both events
        $response->assertJsonFragment(['title' => 'Restricted Event']);
        $response->assertJsonFragment(['title' => 'Public Event CA']);
    }

    public function test_ca_admin_sees_all_demandes_regardless_of_event_roles()
    {
        // given - Create CA role and assign to user
        $caRole = Role::firstOrCreate(['name' => 'CA', 'guard_name' => 'web']);
        $otherRole = Role::factory()->create(['name' => 'other_role_ca2']);

        $this->user->assignRole('CA');

        // Event with different role
        $restrictedEvent = Evenement::factory()->create(['start_at' => now()->addDay()]);
        $restrictedEvent->roles()->attach($otherRole->idRole);

        // Demande linked to restricted event
        $restrictedDemande = Tache::factory()->create([
            'titre' => 'Restricted Dem',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'idEvenement' => $restrictedEvent->idEvenement,
        ]);

        // Orphan demande
        $orphanDemande = Tache::factory()->create([
            'titre' => 'Orphan Dem CA',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'idEvenement' => null,
        ]);

        // when
        $response = $this->actingAs($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then - CA should see all demandes
        $response->assertJsonFragment(['title' => 'Restricted Dem']);
        $response->assertJsonFragment(['title' => 'Orphan Dem CA']);
    }

    public function test_non_ca_user_cannot_see_events_with_different_roles()
    {
        // given - User with parent role (not CA)
        $parentRole = Role::factory()->create(['name' => 'parent_test']);
        $otherRole = Role::factory()->create(['name' => 'other_test']);
        $this->user->rolesCustom()->attach($parentRole->idRole, ['model_type' => Utilisateur::class]);

        // Event with different role
        $restrictedEvent = Evenement::factory()->create([
            'titre' => 'Restricted Only',
            'start_at' => now()->addDay(),
        ]);
        $restrictedEvent->roles()->attach($otherRole->idRole);

        // when - use actingAsNonAdmin to test role filtering without admin bypass
        $response = $this->actingAsNonAdmin($this->user)->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then - Non-CA user should NOT see restricted event
        $response->assertJsonMissing(['title' => 'Restricted Only']);
    }
}
