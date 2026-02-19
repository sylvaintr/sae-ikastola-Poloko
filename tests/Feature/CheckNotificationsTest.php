<?php
namespace Tests\Feature;

use App\Models\DocumentObligatoire;
use App\Models\Evenement;
use App\Models\NotificationSetting;
use App\Models\Utilisateur;
use App\Notifications\SendNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        // On fixe la date pour avoir des calculs précis
        Carbon::setTestNow(Carbon::create(2026, 2, 11, 10, 0, 0));
    }

    /** @test */
    public function it_does_nothing_if_no_active_rules()
    {
        NotificationSetting::create([
            'title'         => 'Test Rule',
            'target_type'   => 'App\Models\Evenement',
            'is_active'     => false, // Inactive
            'reminder_days' => 2,
        ]);

        $this->artisan('notifications:check')
            ->expectsOutput('Aucune règle active trouvée.')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_sends_notifications_for_events_at_exactly_j_minus_x()
    {
        // 1. Créer une règle : Rappel à J-2
        NotificationSetting::create([
            'title'         => 'Rappel Event',
            'target_type'   => 'App\Models\Evenement',
            'target_id'     => 0,
            'is_active'     => true,
            'reminder_days' => 2,
        ]);

        // 2. Créer un utilisateur
        $user = Utilisateur::factory()->create();

        // 3. Créer un événement dans 2 jours (donc rappel aujourd'hui)
        $event = Evenement::create([
            'titre' => 'Fête de l\'école',
            'dateE' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ]);

        // 4. Créer un événement dans 5 jours (pas de rappel aujourd'hui)
        Evenement::create([
            'titre' => 'Réunion',
            'dateE' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $this->artisan('notifications:check')
            ->assertExitCode(0);

        // Vérifier qu'une notif est envoyée pour la fête mais pas la réunion
        Notification::assertSentTo($user, SendNotification::class, function ($notification) use ($event) {
            return $notification->data['event_id'] === $event->idEvenement
            && $notification->data['title'] === "Rappel : Fête de l'école";
        });
    }

    /** @test */
    public function it_does_not_send_duplicate_event_notifications_the_same_day()
    {
        $setting = NotificationSetting::create([
            'title'         => 'Rappel Event',
            'target_type'   => 'App\Models\Evenement',
            'target_id'     => 0,
            'is_active'     => true,
            'reminder_days' => 1,
        ]);

        $user  = Utilisateur::factory()->create();
        $event = Evenement::create([
            'titre' => 'Unique',
            'dateE' => now()->addDay()->format('Y-m-d'),
        ]);

        // Simuler une notification déjà envoyée aujourd'hui pour cet event
        $user->notifications()->create([
            'id'   => \Illuminate\Support\Str::uuid(),
            'type' => SendNotification::class,
            'data' => [
                'event_id'   => $event->idEvenement,
                'event_date' => now()->addDay()->format('Y-m-d'),
            ],
        ]);

        $this->artisan('notifications:check');

        // On vérifie qu'aucune nouvelle notification n'a été envoyée au user (car déjà fait)
        Notification::assertNotSentTo($user, SendNotification::class);
    }

    /** @test */
    public function it_sends_notifications_for_missing_documents_based_on_user_role()
    {
        // 1. Règle Document
        NotificationSetting::create([
            'title'           => 'Check Docs',
            'target_type'     => 'App\Models\DocumentObligatoire',
            'target_id'       => 0,
            'is_active'       => true,
            'recurrence_days' => 7,
        ]);

        // 2. Créer un rôle et un document lié à ce rôle
        $role = \App\Models\Role::create(['name' => 'Parent', 'guard_name' => 'web']);
        $doc  = DocumentObligatoire::create(['nom' => 'Assurance']);
        $doc->roles()->attach($role->idRole);

        // 3. Créer deux utilisateurs : un avec le rôle, un sans
        $userCible = Utilisateur::factory()->create();
        $userCible->assignRole($role);

        $userHorsCible = Utilisateur::factory()->create();

        $this->artisan('notifications:check');

        // L'utilisateur avec le rôle doit recevoir une notif (doc manquant)
        Notification::assertSentTo($userCible, SendNotification::class);

        // L'utilisateur sans le rôle ne doit rien recevoir
        Notification::assertNotSentTo($userHorsCible, SendNotification::class);
    }

    /** @test */
    public function it_respects_document_recurrence_period()
    {
        $setting = NotificationSetting::create([
            'title'           => 'Check Docs Recurrence',
            'target_type'     => 'App\Models\DocumentObligatoire',
            'target_id'       => 0,
            'is_active'       => true,
            'recurrence_days' => 5, // Rappel tous les 5 jours
        ]);

        $role = \App\Models\Role::create(['name' => 'Admin']);
        $doc  = DocumentObligatoire::create(['nom' => 'Certificat']);
        $doc->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->assignRole($role);

        // Simuler une notif envoyée il y a 2 jours
        $user->notifications()->create([
            'id'         => \Illuminate\Support\Str::uuid(),
            'type'       => SendNotification::class,
            'data'       => ['doc_id' => $doc->idDocumentObligatoire, 'title' => "Document manquant : Certificat"],
            'created_at' => now()->subDays(2),
        ]);

        // Exécuter la commande
        $this->artisan('notifications:check');

        // Ne doit rien envoyer car on est à J+2 et la récurrence est à J+5
        Notification::assertNothingSent();

        // On avance le temps à J+6
        Carbon::setTestNow(now()->addDays(4));

        $this->artisan('notifications:check');

        // Cette fois, la notif doit partir
        Notification::assertSentTo($user, SendNotification::class);
    }
}
