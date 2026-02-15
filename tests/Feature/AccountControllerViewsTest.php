<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountController;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_returns_view(): void
    {
        // given
        $controller = new AccountController();

        // when
        $response = $controller->create();

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
    }

    public function test_show_and_edit_loads_account(): void
    {
        // given
        $user = Utilisateur::factory()->create();
        $controller = new AccountController();

        // when
        $respShow = $controller->show($user);
        $respEdit = $controller->edit($user);

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $respShow);
        $this->assertInstanceOf(\Illuminate\View\View::class, $respEdit);
    }
}
