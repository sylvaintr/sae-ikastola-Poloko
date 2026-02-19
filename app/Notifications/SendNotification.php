<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Constructeur pour initialiser les données de la notification. Ce constructeur prend un tableau de données en paramètre, qui contient les informations nécessaires pour construire la notification (comme le titre, le message, l'URL d'action, etc.). Ces données sont ensuite utilisées dans la méthode `toArray` pour formater la notification avant de l'enregistrer dans la base de données ou de l'envoyer à l'utilisateur.
     * @param array $data Un tableau associatif contenant les données de la notification, avec des clés telles que 'title', 'message', 'action_url', etc.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Détermine les canaux de notification à utiliser pour envoyer la notification. Dans ce cas, la méthode retourne un tableau contenant le canal 'database', ce qui signifie que la notification sera enregistrée dans la base de données et pourra être récupérée et affichée à l'utilisateur via une interface de notifications. D'autres canaux possibles pourraient inclure 'mail' pour les notifications par e-mail, 'broadcast' pour les notifications en temps réel, etc.
     * @param mixed $notifiable L'entité à laquelle la notification est destinée (par exemple, un utilisateur). Ce paramètre peut être utilisé pour personnaliser les canaux de notification en fonction des préférences de l'utilisateur ou du type d'entité.
     * @return array Un tableau de chaînes de caractères représentant les canaux de notification à utiliser (dans ce cas, ['database']).
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Formate la notification pour l'enregistrement dans la base de données. Cette méthode retourne un tableau associatif contenant les données de la notification formatées de manière appropriée pour être stockées dans la base de données. Les clés du tableau correspondent aux champs attendus par le système de notifications, tels que 'title' pour le titre de la notification, 'message' pour le contenu du message, 'action_url' pour l'URL d'action associée à la notification, et 'icon' pour l'icône à afficher avec la notification. Ces données sont utilisées pour afficher la notification à l'utilisateur de manière cohérente et informative.
     * @param mixed $notifiable L'entité à laquelle la notification est destinée (par exemple, un utilisateur). Ce paramètre peut être utilisé pour personnaliser les données de la notification en fonction des préférences de l'utilisateur ou du type d'entité.
     * @return array Un tableau associatif contenant les données de la notification formatées pour l'enregistrement dans la base de données, avec des clés telles que 'title', 'message', 'action_url', et 'icon'.
     */
    public function toArray($notifiable)
    {
        return [
            'title'      => $this->data['title'],
            'message'    => $this->data['message'],
            'action_url' => $this->data['action_url'] ?? '#',
            'icon'       => 'bi-info-circle',
        ];
    }
}
