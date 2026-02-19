<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Méthode pour construire le message de notification par e-mail pour la réinitialisation du mot de passe. Cette méthode personnalise le contenu de l'e-mail en fonction de la langue préférée de l'utilisateur (déterminée à partir de la session, de la préférence utilisateur ou de la configuration par défaut). Le message inclut une salutation, un sujet, des lignes d'introduction et de conclusion, ainsi qu'un bouton d'action qui redirige vers la page de réinitialisation du mot de passe avec le token et l'e-mail de l'utilisateur. Cette personnalisation permet d'offrir une expérience utilisateur adaptée à la langue préférée de chaque utilisateur lors de la réception des notifications par e-mail.
     * @param mixed $notifiable L'entité qui reçoit la notification, généralement un utilisateur. Cette entité doit implémenter la méthode `getEmailForPasswordReset()` pour fournir l'adresse e-mail à laquelle envoyer la notification de réinitialisation du mot de passe.
     * @return MailMessage Le message de notification par e-mail personnalisé pour la réinitialisation du mot de passe, prêt à être envoyé à l'utilisateur. Le message contient une salutation, un sujet, des lignes d'introduction et de conclusion, ainsi qu'un bouton d'action avec le lien de réinitialisation du mot de passe.
     */
    public function toMail($notifiable): MailMessage
    {
        $locale = session('locale') ?? $notifiable->languePref ?? config('app.locale');

        if (! in_array($locale, ['fr', 'eus'], true)) {
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
