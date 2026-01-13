<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use App\Notifications\ResetPasswordNotification;

class ResetPasswordNotificationTest extends TestCase
{
    public function test_toMail_builds_mailmessage_and_uses_notifiable_language()
    {
        $notifiable = new class {
            public $languePref = 'eus';
            public function getEmailForPasswordReset() { return 'user@example.com'; }
        };

        $token = 'tok123';
        $notification = new ResetPasswordNotification($token);

        $mail = $notification->toMail($notifiable);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertEquals(__('auth.reinitialiser_mot_de_passe'), $mail->subject);
        $this->assertEquals(__('auth.notification_reset_line1'), $mail->introLines[0]);
        $this->assertEquals(__('auth.notification_reset_line2'), $mail->outroLines[0]);
        $this->assertEquals(__('auth.reinitialiser_mot_de_passe'), $mail->actionText);

        // actionUrl should contain token and email (email may be URL-encoded)
        $parts = parse_url($mail->actionUrl);
        $this->assertStringContainsString($token, $parts['path']);
        parse_str($parts['query'] ?? '', $qs);
        $this->assertEquals('user@example.com', urldecode($qs['email'] ?? ''));
    }

    public function test_toMail_uses_session_locale_over_user_pref()
    {
        $this->withSession(['locale' => 'fr']);

        $notifiable = new class {
            public $languePref = 'eus';
            public function getEmailForPasswordReset() { return 'u2@example.com'; }
        };

        $notification = new ResetPasswordNotification('t');
        $mail = $notification->toMail($notifiable);

        $this->assertEquals('fr', app()->getLocale());
        $this->assertEquals(__('auth.reinitialiser_mot_de_passe'), $mail->subject);
    }

    public function test_toMail_falls_back_to_config_locale_for_unsupported()
    {
        $notifiable = new class {
            public $languePref = 'en';
            public function getEmailForPasswordReset() { return 'u3@example.com'; }
        };

        $notification = new ResetPasswordNotification('t2');
        $mail = $notification->toMail($notifiable);

        $this->assertEquals(config('app.locale'), app()->getLocale());
        $this->assertEquals(__('auth.reinitialiser_mot_de_passe'), $mail->subject);
    }
}
