<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisteredUserRecaptchaTest extends TestCase
{
    use RefreshDatabase;
    public const EXEMPLE_MOT_DE_PASSE = 'P@ssw0rd123!';

    public function test_registration_fails_when_recaptcha_required_but_missing()
    {
        // Enable recaptcha and ensure secret key is empty => verifyRecaptcha will return false
        config(['services.recaptcha.enabled' => true]);
        config(['services.recaptcha.secret_key' => null]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'recaptcha@example.test',
            'password' => self::EXEMPLE_MOT_DE_PASSE,
            'password_confirmation' => self::EXEMPLE_MOT_DE_PASSE,
            // no g-recaptcha-response provided
        ]);

        // Should redirect back with recaptcha error
        $response->assertRedirect();
        $response->assertSessionHasErrors(['g-recaptcha-response']);
    }

    public function test_registration_succeeds_with_recaptcha_in_local_test_keys()
    {
        // Enable recaptcha and set test keys so verifyRecaptcha accepts non-empty responses in local env
        config(['services.recaptcha.enabled' => true]);
        config(['services.recaptcha.secret_key' => 'test_secret']);
        config(['services.recaptcha.test_secret_key' => 'test_secret']);
        config(['app.env' => 'local']);

        $email = 'recaptcha-success+' . uniqid() . '@example.test';

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' =>  self::EXEMPLE_MOT_DE_PASSE,
            'password_confirmation' => self::EXEMPLE_MOT_DE_PASSE,
            'g-recaptcha-response' => 'nonempty',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('utilisateur', ['email' => $email]);
    }
    }

