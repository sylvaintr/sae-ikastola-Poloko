<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Utilisateur;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;
    protected $pathtested = '/login';

    public function test_regles_retournent_structure_attendue()
    {
        // given
        // none

        // when

        // then
        $base = HttpRequest::create($this->pathtested, 'POST', ['email' => 'a@b.test', 'password' => 'secret']);
        $req = LoginRequest::createFromBase($base);

        $rules = $req->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_authentification_reussie_efface_rate_limiter()
    {
        // given
        // none

        // when

        // then
        // Arrange request with credentials
        $email = 'ok+' . uniqid() . '@example.test';
        Utilisateur::factory()->create(['email' => $email, 'archived_at' => null]);
        $base = HttpRequest::create($this->pathtested, 'POST', ['email' => $email, 'password' => 'secret', 'remember' => '0']);
        $req = LoginRequest::createFromBase($base);

        // Mock RateLimiter and Auth behaviors
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->with(['email' => $email, 'password' => 'secret'], false)->andReturn(true);
        RateLimiter::shouldReceive('clear')->once()->with($req->throttleKey());

        // Act
        $req->authenticate();

        $this->addToAssertionCount(1); // authenticate() has no return value; assertions are via mocks
    }

    public function test_authentification_rejette_compte_archive()
    {
        // given
        // none

        // when

        // then
        $email = 'archived+' . uniqid() . '@example.test';
        Utilisateur::factory()->create(['email' => $email, 'archived_at' => now()]);
        $base = HttpRequest::create($this->pathtested, 'POST', ['email' => $email, 'password' => 'secret']);
        $req = LoginRequest::createFromBase($base);

        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        Auth::shouldReceive('attempt')->never();
        RateLimiter::shouldReceive('hit')->once()->with($req->throttleKey());

        try {
            $req->authenticate();
            $this->fail('Archived account should not authenticate.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            $this->assertEquals(trans('auth.failed'), $e->errors()['email'][0]);
        }
    }

    public function test_ensureIsNotRateLimited_lance_si_rate_limite()
    {
        // given
        // none

        // when

        // then
        $email = 'rl+' . uniqid() . '@example.test';
        $base = HttpRequest::create($this->pathtested, 'POST', ['email' => $email]);
        $req = LoginRequest::createFromBase($base);

        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->once()->andReturn(30);

        $this->expectException(ValidationException::class);
        $req->ensureIsNotRateLimited();
    }

    public function test_throttleKey_genere_format_attendu()
    {
        // given
        // none

        // when

        // then
        $email = 'Mix.Case+test@example.test';
        $ip = '127.0.0.1';
        $base = HttpRequest::create($this->pathtested, 'POST', ['email' => $email]);
        $base->server->set('REMOTE_ADDR', $ip);
        $req = LoginRequest::createFromBase($base);

        $key = $req->throttleKey();

        $this->assertStringContainsString('|' . $ip, $key);
        $this->assertStringNotContainsString(' ', $key);
    }
}
