<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Http\Controllers\Admin\AccountController;

class AdminAccountArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_sets_archived_at_when_not_archived()
    {
        $user = Utilisateur::factory()->create();
        $controller = new AccountController();

        $resp = $controller->archive($user);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertTrue($user->fresh()->isArchived());
    }

    public function test_archive_redirects_when_already_archived()
    {
        $user = Utilisateur::factory()->create(['archived_at' => now()]);
        $controller = new AccountController();

        $resp = $controller->archive($user);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertTrue($user->fresh()->isArchived());
    }
}
