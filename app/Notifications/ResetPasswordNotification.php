<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        // Priorité : langue en base > langue session > langue par défaut
        $locale = session('locale')
            ?? $notifiable->languePref
            ?? config('app.locale');

        if (!in_array($locale, ['fr', 'eus'], true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->greeting(__('mail.greeting'))
            ->subject(__('auth.reinitialiser_mot_de_passe'))
            ->line(__('auth.notification_reset_line1'))
            ->action(__('auth.reinitialiser_mot_de_passe'), $url)
            ->line(__('auth.notification_reset_line2'))
            ->salutation(__('mail.salutation') . "\n" . config('app.name'));
    }
}
