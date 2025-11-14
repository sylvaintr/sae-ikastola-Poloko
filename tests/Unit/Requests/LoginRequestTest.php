<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\Auth\LoginRequest;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rules_return_expected_structure()
    {
        $base = HttpRequest::create('/login', 'POST', ['email' => 'a@b.test', 'password' => 'secret']);
        $req = LoginRequest::createFromBase($base);

        $rules = $req->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_authenticate_success_clears_rate_limiter()
    {
        // Arrange request with credentials
        $email = 'ok+' . uniqid() . '@example.test';
        $base = HttpRequest::create('/login', 'POST', ['email' => $email, 'password' => 'secret', 'remember' => '0']);
        $req = LoginRequest::createFromBase($base);

        // Mock RateLimiter and Auth behaviors
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->with(['email' => $email, 'password' => 'secret'], false)->andReturn(true);
        RateLimiter::shouldReceive('clear')->once()->with($req->throttleKey());

        // Act
        $req->authenticate();

        $this->addToAssertionCount(1); // authenticate() has no return value; assertions are via mocks
    }

    public function test_authenticate_failure_hits_rate_limiter_and_throws()
    {
        $email = 'fail+' . uniqid() . '@example.test';
        $base = HttpRequest::create('/login', 'POST', ['email' => $email, 'password' => 'badpass']);
        $req = LoginRequest::createFromBase($base);

        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        Auth::shouldReceive('attempt')->once()->andReturn(false);
        RateLimiter::shouldReceive('hit')->once()->with($req->throttleKey());

        $this->expectException(ValidationException::class);
        $req->authenticate();
    }

    public function test_ensureIsNotRateLimited_throws_when_rate_limited()
    {
        $email = 'rl+' . uniqid() . '@example.test';
        $base = HttpRequest::create('/login', 'POST', ['email' => $email]);
        $req = LoginRequest::createFromBase($base);

        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->once()->andReturn(30);

        $this->expectException(ValidationException::class);
        $req->ensureIsNotRateLimited();
    }

    public function test_throttleKey_generates_expected_format()
    {
        $email = 'Mix.Case+test@example.test';
        $ip = '127.0.0.1';
        $base = HttpRequest::create('/login', 'POST', ['email' => $email]);
        $base->server->set('REMOTE_ADDR', $ip);
        $req = LoginRequest::createFromBase($base);

        $key = $req->throttleKey();

        $this->assertStringContainsString('|' . $ip, $key);
        $this->assertStringNotContainsString(' ', $key);
    }
}
