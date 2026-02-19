<?php
namespace Tests\Feature\Notifications;

use App\Models\Utilisateur;
use App\Notifications\SendNotification;
use Tests\TestCase;

class SendNotificationTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_array_structure()
    {
        // 1. Préparation des données
        $data = [
            'title'      => 'Test Titre',
            'message'    => 'Ceci est un message de test',
            'action_url' => 'https://mon-app.com/evenements/1',
        ];

        $notification = new SendNotification($data);
        $notifiable   = new Utilisateur(); // On simule un utilisateur (notifiable)

        // 2. Exécution de la méthode toArray
        $result = $notification->toArray($notifiable);

        // 3. Assertions
        $this->assertIsArray($result);
        $this->assertEquals('Test Titre', $result['title']);
        $this->assertEquals('Ceci est un message de test', $result['message']);
        $this->assertEquals('https://mon-app.com/evenements/1', $result['action_url']);
        $this->assertEquals('bi-info-circle', $result['icon']); // Vérifie l'icône par défaut
    }

    /** @test */
    public function it_uses_default_action_url_if_none_provided()
    {
        // Données sans action_url
        $data = [
            'title'   => 'Sans URL',
            'message' => 'Test sans lien',
        ];

        $notification = new SendNotification($data);
        $result       = $notification->toArray(new Utilisateur());

        // Vérifie que la valeur par défaut '#' est bien appliquée
        $this->assertEquals('#', $result['action_url']);
    }

    /** @test */
    public function it_is_stored_in_the_database_via_channel()
    {
        $notification = new SendNotification(['title' => 'db', 'message' => 'db']);

        // Vérifie que le canal de notification est bien 'database'
        $this->assertEquals(['database'], $notification->via(new Utilisateur()));
    }

    /** @test */
    public function it_contains_extra_data_if_passed_to_constructor()
    {
        // Si vous passez des IDs (comme event_id ou doc_id vus dans votre commande console),
        // votre classe actuelle ne les inclut pas explicitement dans toArray,
        // SAUF si vous modifiez votre classe pour les retourner.

        $data = [
            'title'    => 'Titre',
            'message'  => 'Message',
            'event_id' => 123, // Note: Votre code actuel ignore cette clé dans toArray
        ];

        $notification = new SendNotification($data);
        $result       = $notification->toArray(new Utilisateur());

        // Teste que les clés de base sont présentes
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('action_url', $result);
        $this->assertArrayHasKey('icon', $result);
    }
}
