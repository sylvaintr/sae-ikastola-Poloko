<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public $link = '/profile';

    public $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = Utilisateur::factory()->create();
    }

    public function test_profile_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get($this->link);

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $email = 'user' . rand(1, 1000) . '@example.com';

        $response = $this
            ->actingAs($this->user)
            ->patch($this->link, [
                'prenom' => 'Test',
                'nom' => 'User',
                'email' => $email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->link);

        $this->user->refresh();

        $this->assertSame('Test', $this->user->prenom);
        $this->assertSame('User', $this->user->nom);
        $this->assertSame($email, $this->user->email);
        $this->assertNull($this->user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {


        $response = $this
            ->actingAs($this->user)
            ->patch($this->link, [
                'name' => 'Test User',
                'email' => $this->user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->link);

        $this->assertNotNull($this->user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {

        $response = $this
            ->actingAs($this->user)
            ->delete($this->link, [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull($this->user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {


        $response = $this
            ->actingAs($this->user)
            ->from($this->link)
            ->delete($this->link, [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect($this->link);

        $this->assertNotNull($this->user->fresh());
    }
}
