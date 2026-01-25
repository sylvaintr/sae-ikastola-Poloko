<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $email = 'test+' . uniqid() . '@example.test';

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'P@ssw0rd123!',
            'password_confirmation' => 'P@ssw0rd123!',
        ]);

        // L'application n'authentifie pas automatiquement les nouveaux comptes
        // (statutValidation = false). Vérifier la redirection vers la page de
        // connexion avec le message d'information en session.
        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
    }
}
