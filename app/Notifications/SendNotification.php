<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification
{
    use Queueable;
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function via($notifiable) {
        return ['database']; 
    }

    public function toArray($notifiable) {
        return [
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'action_url' => $this->data['action_url'] ?? '#',
            'icon' => 'bi-info-circle', 
        ];
    }
}