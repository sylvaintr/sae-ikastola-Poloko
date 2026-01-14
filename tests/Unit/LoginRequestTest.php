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

    public function test_authentification_lance_exception_si_utilisateur_existe_mais_non_valide()
    {
        // given
        $user = Utilisateur::factory()->create([
            'email' => 'notvalid@example.com',
            'statutValidation' => false,
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'notvalid@example.com',
            'password' => 'password',
        ]);

        $request->setContainer($this->app);

        // when
        $caught = null;
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            $caught = $e;
        }

        // then
        $this->assertNotNull($caught);
        $messages = $caught->errors();
        $this->assertArrayHasKey('email', $messages);
        $this->assertEquals(trans('auth.account_not_validated'), $messages['email'][0]);
    }
}
