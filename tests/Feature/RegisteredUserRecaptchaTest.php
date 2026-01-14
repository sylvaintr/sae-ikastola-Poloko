<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisteredUserRecaptchaTest extends TestCase
{
    use RefreshDatabase;
    public const EXEMPLE_MOT_DE_PASSE = 'P@ssw0rd123!';

    public function test_inscription_echoue_si_recaptcha_requis_manquant()
    {
        // given
        // Enable recaptcha and ensure secret key is empty => verifyRecaptcha will return false
        config(['services.recaptcha.enabled' => true]);
        config(['services.recaptcha.secret_key' => null]);

        // when
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'recaptcha@example.test',
            'password' => self::EXEMPLE_MOT_DE_PASSE,
            'password_confirmation' => self::EXEMPLE_MOT_DE_PASSE,
            // no g-recaptcha-response provided
        ]);

        // then
        // Should redirect back with recaptcha error
        $response->assertRedirect();
        $response->assertSessionHasErrors(['g-recaptcha-response']);
    }

    public function test_inscription_reussit_avec_recaptcha_cles_locales_de_test()
    {
        // given
        // Enable recaptcha and set test keys so verifyRecaptcha accepts non-empty responses in local env
        config(['services.recaptcha.enabled' => true]);
        config(['services.recaptcha.secret_key' => 'test_secret']);
        config(['services.recaptcha.test_secret_key' => 'test_secret']);
        config(['app.env' => 'local']);

        $email = 'recaptcha-success+' . uniqid() . '@example.test';

        // when
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' =>  self::EXEMPLE_MOT_DE_PASSE,
            'password_confirmation' => self::EXEMPLE_MOT_DE_PASSE,
            'g-recaptcha-response' => 'nonempty',
        ]);

        // then
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('utilisateur', ['email' => $email]);
    }
    }

