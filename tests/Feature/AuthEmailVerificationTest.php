<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Mockery;
use App\Models\Utilisateur;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;

class AuthEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_shows_view_when_not_verified()
    {
        $user = Mockery::mock(Utilisateur::class)->makePartial();
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertViewIs('auth.verify-email');
    }

    public function test_prompt_redirects_when_verified()
    {
        $user = Mockery::mock(Utilisateur::class)->makePartial();
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect(route('home'));
    }

    public function test_notification_sends_when_not_verified()
    {
        $user = Mockery::mock(Utilisateur::class)->makePartial();
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $user->shouldReceive('sendEmailVerificationNotification')->once();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-link-sent');
    }

    public function test_notification_redirects_when_verified()
    {
        $user = Mockery::mock(Utilisateur::class)->makePartial();
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('home'));
    }

    public function test_verify_marks_email_and_redirects()
    {
        Event::fake();

        $mockRequest = Mockery::mock(EmailVerificationRequest::class)->makePartial();

        $user = Mockery::mock(Utilisateur::class)->makePartial();
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $user->shouldReceive('markEmailAsVerified')->andReturn(true);

        $mockRequest->shouldReceive('user')->andReturn($user);

        $controller = new VerifyEmailController();

        $response = $controller->__invoke($mockRequest);

        $this->assertStringContainsString('?verified=1', $response->getTargetUrl());

        Event::assertDispatched(Verified::class);
    }

    public function test_verify_redirects_when_already_verified()
    {
        Event::fake();

        $mockRequest = Mockery::mock(EmailVerificationRequest::class)->makePartial();

        $user = Mockery::mock(Utilisateur::class)->makePartial();
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);

        $mockRequest->shouldReceive('user')->andReturn($user);

        $controller = new VerifyEmailController();

        $response = $controller->__invoke($mockRequest);

        $this->assertStringContainsString('?verified=1', $response->getTargetUrl());

        Event::assertNotDispatched(Verified::class);
    }
}
