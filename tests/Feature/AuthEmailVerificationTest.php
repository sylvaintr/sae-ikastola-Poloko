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
        // given
        $user = Utilisateur::factory()->unverified()->create();

        // when
        $response = $this->actingAs($user)->get(route('verification.notice'));

        // then
        $response->assertOk()->assertViewIs('auth.verify-email');
    }

    public function test_prompt_redirects_when_verified()
    {
        // given
        $user = Utilisateur::factory()->create();

        // when
        $response = $this->actingAs($user)->get(route('verification.notice'));

        // then
        $response->assertRedirect(route('home'));
    }

    public function test_notification_sends_when_not_verified()
    {
        // given
        $user = Utilisateur::factory()->unverified()->create();

        // when
        $response = $this->actingAs($user)->post(route('verification.send'));

        // then
        $response->assertSessionHas('status', 'verification-link-sent');
    }

    public function test_notification_redirects_when_verified()
    {
        // given
        $user = Utilisateur::factory()->create();

        // when
        $response = $this->actingAs($user)->post(route('verification.send'));

        // then
        $response->assertRedirect(route('home'));
    }

    public function test_verify_marks_email_and_redirects()
    {
        // given
        Event::fake();

        $mockRequest = Mockery::mock(EmailVerificationRequest::class)->makePartial();

        $user = Utilisateur::factory()->unverified()->create();

        $mockRequest->shouldReceive('user')->andReturn($user);

        $controller = new VerifyEmailController();

        // when
        $response = $controller->__invoke($mockRequest);

        // then
        $this->assertStringContainsString('?verified=1', $response->getTargetUrl());
        Event::assertDispatched(Verified::class);
    }

    public function test_verify_redirects_when_already_verified()
    {
        // given
        Event::fake();

        $mockRequest = Mockery::mock(EmailVerificationRequest::class)->makePartial();

        $user = Utilisateur::factory()->create();

        $mockRequest->shouldReceive('user')->andReturn($user);

        $controller = new VerifyEmailController();

        // when
        $response = $controller->__invoke($mockRequest);

        // then
        $this->assertStringContainsString('?verified=1', $response->getTargetUrl());
        Event::assertNotDispatched(Verified::class);
    }
}
