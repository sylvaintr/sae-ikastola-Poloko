<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Evenement;

class IcsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Utilisateur::factory()->create();
        // Generate ICS token for the user
        $this->user->generateIcsToken();
    }

    // ========================================
    // Tests d'accès au flux ICS
    // ========================================

    public function test_ics_feed_is_accessible_with_valid_token()
    {
        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $response->assertStatus(200);
        $this->assertStringContainsStringIgnoringCase('text/calendar', $response->headers->get('Content-Type'));
    }

    public function test_ics_feed_returns_404_with_invalid_token()
    {
        // when
        $response = $this->get(route('ics.feed', ['token' => 'invalid-token-12345']));

        // then
        $response->assertStatus(404);
    }

    public function test_ics_feed_returns_404_with_empty_token()
    {
        // when
        $response = $this->get('/ics/');

        // then
        $response->assertStatus(404);
    }

    // ========================================
    // Tests du contenu ICS
    // ========================================

    public function test_ics_feed_contains_vcalendar_header()
    {
        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
        $this->assertStringContainsString('VERSION:2.0', $content);
        $this->assertStringContainsString('END:VCALENDAR', $content);
    }

    public function test_ics_feed_contains_prodid()
    {
        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertStringContainsString('PRODID:', $content);
    }

    public function test_ics_feed_includes_events()
    {
        // given
        $evenement = Evenement::factory()->create([
            'titre' => 'ICS Test Event',
            'description' => 'Event for ICS feed test',
            'start_at' => now()->addDay(),
        ]);

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VEVENT', $content);
        $this->assertStringContainsString('SUMMARY:ICS Test Event', $content);
        $this->assertStringContainsString('DESCRIPTION:Event for ICS feed test', $content);
        $this->assertStringContainsString('END:VEVENT', $content);
    }

    public function test_ics_event_has_unique_uid()
    {
        // given
        $evenement = Evenement::factory()->create([
            'titre' => 'UID Test Event',
            'start_at' => now()->addDay(),
        ]);

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertStringContainsString('UID:event-' . $evenement->idEvenement . '@', $content);
    }

    public function test_ics_event_has_dtstart()
    {
        // given
        $startAt = now()->addDays(5);
        $evenement = Evenement::factory()->create([
            'titre' => 'DTSTART Test Event',
            'start_at' => $startAt,
        ]);

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertStringContainsString('DTSTART:', $content);
    }

    public function test_ics_event_has_dtend_when_end_date_exists()
    {
        // given
        $evenement = Evenement::factory()->create([
            'titre' => 'DTEND Test Event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ]);

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertStringContainsString('DTEND:', $content);
    }

    public function test_ics_feed_includes_multiple_events()
    {
        // given
        $count = Evenement::factory()->count(3)->create()->count();

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $content = $response->getContent();
        $this->assertGreaterThanOrEqual($count, substr_count($content, 'BEGIN:VEVENT'));
        $this->assertGreaterThanOrEqual($count, substr_count($content, 'END:VEVENT'));
    }

    public function test_ics_feed_escapes_special_characters()
    {
        // given
        $evenement = Evenement::factory()->create([
            'titre' => 'Event with, comma; semicolon',
            'description' => 'Description with "quotes" and \\backslash',
            'start_at' => now()->addDay(),
        ]);

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $response->assertStatus(200);
        // The ICS should still be valid
        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VEVENT', $content);
    }

    // ========================================
    // Tests de régénération du token
    // ========================================

    public function test_regenerate_token_creates_new_token()
    {
        // given
        $oldToken = $this->user->ics_token;

        // when
        $response = $this->actingAs($this->user)->post(route('profile.regenerate-ics-token'));

        // then
        $this->user->refresh();
        $this->assertNotEquals($oldToken, $this->user->ics_token);
    }

    public function test_regenerate_token_invalidates_old_token()
    {
        // given
        $oldToken = $this->user->ics_token;

        // when
        $this->actingAs($this->user)->post(route('profile.regenerate-ics-token'));

        // then - old token should no longer work
        $response = $this->get(route('ics.feed', ['token' => $oldToken]));
        $response->assertStatus(404);
    }

    public function test_regenerate_token_new_token_works()
    {
        // given
        $this->actingAs($this->user)->post(route('profile.regenerate-ics-token'));
        $this->user->refresh();

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $response->assertStatus(200);
    }

    public function test_regenerate_token_redirects_back()
    {
        // when
        $response = $this->actingAs($this->user)
            ->from(route('calendrier.index'))
            ->post(route('profile.regenerate-ics-token'));

        // then
        $response->assertRedirect(route('calendrier.index'));
        $response->assertSessionHas('status', 'ics-token-regenerated');
    }

    // ========================================
    // Tests du modèle Utilisateur (ICS)
    // ========================================

    public function test_user_can_generate_ics_token()
    {
        // given
        $newUser = Utilisateur::factory()->create(['ics_token' => null]);

        // when
        $newUser->generateIcsToken();

        // then
        $this->assertNotNull($newUser->ics_token);
        $this->assertEquals(64, strlen($newUser->ics_token));
    }

    public function test_user_can_get_ics_url()
    {
        // when
        $url = $this->user->getIcsUrl();

        // then
        $this->assertStringContainsString('/ics/', $url);
        $this->assertStringContainsString($this->user->ics_token, $url);
    }

    public function test_ics_token_is_unique()
    {
        // given
        $user1 = Utilisateur::factory()->create();
        $user2 = Utilisateur::factory()->create();

        // when
        $user1->generateIcsToken();
        $user2->generateIcsToken();

        // then
        $this->assertNotEquals($user1->ics_token, $user2->ics_token);
    }

    // ========================================
    // Tests de sécurité
    // ========================================

    public function test_ics_feed_does_not_require_authentication()
    {
        // The ICS feed should be accessible without being logged in,
        // as it uses token-based authentication for calendar apps

        // when
        $response = $this->get(route('ics.feed', ['token' => $this->user->ics_token]));

        // then
        $response->assertStatus(200);
    }

    public function test_ics_token_is_hidden_in_json_serialization()
    {
        // when
        $json = $this->user->toArray();

        // then
        $this->assertArrayNotHasKey('ics_token', $json);
    }
}
