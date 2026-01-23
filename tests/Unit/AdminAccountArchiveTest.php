<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Http\Controllers\Admin\AccountController;

class AdminAccountArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_definit_archive_si_non_archive()
    {
        // given
        $user = Utilisateur::factory()->create();
        $controller = new AccountController();

        // when
        $resp = $controller->archive($user);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertTrue($user->fresh()->isArchived());
    }

    public function test_archive_redirige_si_deja_archive()
    {
        // given
        $user = Utilisateur::factory()->create(['archived_at' => now()]);
        $controller = new AccountController();

        // when
        $resp = $controller->archive($user);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertTrue($user->fresh()->isArchived());
    }
}
