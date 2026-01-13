<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Utilisateur;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticate_throws_if_user_exists_but_not_validated()
    {
        $user = Utilisateur::factory()->create([
            'email' => 'notvalid@example.com',
            'statutValidation' => false,
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'notvalid@example.com',
            'password' => 'password',
        ]);

        $request->setContainer($this->app);

        $this->expectException(ValidationException::class);

        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            $messages = $e->errors();
            $this->assertArrayHasKey('email', $messages);
            $this->assertEquals(trans('auth.account_not_validated'), $messages['email'][0]);
            throw $e;
        }
    }
}
