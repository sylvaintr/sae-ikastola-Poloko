<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Tache;

class CalendrierDemandesTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Utilisateur::factory()->create();
    }

    // ========================================
    // Tests des demandes sur le calendrier
    // ========================================

    public function test_demandes_en_attente_appear_on_calendar()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Demande En Attente',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Demande En Attente']);
    }

    public function test_demandes_en_cours_appear_on_calendar()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Demande En Cours',
            'etat' => 'En cours',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Demande En Cours']);
    }

    public function test_demandes_terminees_do_not_appear_on_calendar()
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
        $response->assertJsonMissing(['title' => 'Demande Terminée']);
    }

    public function test_demandes_are_marked_as_all_day()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'All Day Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'All Day Demande');
        $this->assertNotNull($event);
        $this->assertTrue($event['allDay']);
    }

    public function test_demandes_have_type_demande_in_extended_props()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Type Check Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'Type Check Demande');
        $this->assertEquals('demande', $event['extendedProps']['type']);
    }

    public function test_demandes_include_urgence_in_extended_props()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Urgent Demande',
            'etat' => 'En attente',
            'urgence' => 'Élevée',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'Urgent Demande');
        $this->assertEquals('Élevée', $event['extendedProps']['urgence']);
    }

    public function test_demandes_include_etat_in_extended_props()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Etat Check Demande',
            'etat' => 'En cours',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'Etat Check Demande');
        $this->assertEquals('En cours', $event['extendedProps']['etat']);
    }

    public function test_demandes_id_is_prefixed_with_demande()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'ID Prefix Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'ID Prefix Demande');
        $this->assertStringStartsWith('demande-', $event['id']);
        $this->assertEquals('demande-' . $demande->idTache, $event['id']);
    }

    public function test_demandes_with_date_range_are_displayed_correctly()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Multi Day Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'dateF' => now()->addDays(5),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'Multi Day Demande');
        $this->assertNotNull($event);
        $this->assertNotNull($event['start']);
        $this->assertNotNull($event['end']);
    }

    public function test_demandes_without_end_date_have_null_end()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'No End Date Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDay(),
            'dateF' => null,
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();
        $event = collect($json)->firstWhere('title', 'No End Date Demande');
        $this->assertNull($event['end']);
    }

    // ========================================
    // Tests de filtrage par date des demandes
    // ========================================

    public function test_demandes_outside_date_range_are_excluded()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Future Demande',
            'etat' => 'En attente',
            'dateD' => now()->addMonths(3),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonMissing(['title' => 'Future Demande']);
    }

    public function test_demandes_spanning_date_range_are_included()
    {
        // given
        $this->actingAs($this->user);
        $demande = Tache::factory()->create([
            'titre' => 'Spanning Demande',
            'etat' => 'En attente',
            'dateD' => now()->subWeek(),
            'dateF' => now()->addWeek(),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Spanning Demande']);
    }

    // ========================================
    // Tests de mélange événements et demandes
    // ========================================

    public function test_calendar_shows_both_events_and_demandes()
    {
        // given
        $this->actingAs($this->user);

        $evenement = \App\Models\Evenement::factory()->create([
            'titre' => 'Calendar Event',
            'start_at' => now()->addDays(2),
        ]);

        $demande = Tache::factory()->create([
            'titre' => 'Calendar Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDays(3),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $response->assertJsonFragment(['title' => 'Calendar Event']);
        $response->assertJsonFragment(['title' => 'Calendar Demande']);
    }

    public function test_events_and_demandes_have_different_id_prefixes()
    {
        // given
        $this->actingAs($this->user);

        $evenement = \App\Models\Evenement::factory()->create([
            'titre' => 'Prefixed Event',
            'start_at' => now()->addDays(2),
        ]);

        $demande = Tache::factory()->create([
            'titre' => 'Prefixed Demande',
            'etat' => 'En attente',
            'dateD' => now()->addDays(3),
        ]);

        // when
        $response = $this->getJson(route('calendrier.events', [
            'start' => now()->toISOString(),
            'end' => now()->addMonth()->toISOString(),
        ]));

        // then
        $json = $response->json();

        $event = collect($json)->firstWhere('title', 'Prefixed Event');
        $demandeEvent = collect($json)->firstWhere('title', 'Prefixed Demande');

        $this->assertStringStartsWith('event-', $event['id']);
        $this->assertStringStartsWith('demande-', $demandeEvent['id']);
    }
}
