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
        $controller = new AccountController();
        $response = $controller->create();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
    }

    public function test_show_and_edit_loads_account(): void
    {
        $user = Utilisateur::factory()->create();
        $controller = new AccountController();

        $respShow = $controller->show($user);
        $this->assertInstanceOf(\Illuminate\View\View::class, $respShow);

        $respEdit = $controller->edit($user);
        $this->assertInstanceOf(\Illuminate\View\View::class, $respEdit);
    }
}
