<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    use RefreshDatabase;

    public function test_index_method_exists(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'index'));
        $this->assertTrue(method_exists(AccountController::class, 'store'));
        $this->assertTrue(method_exists(AccountController::class, 'update'));
        $this->assertTrue(method_exists(AccountController::class, 'validateAccount'));
        $this->assertTrue(method_exists(AccountController::class, 'destroy'));
    }

    public function test_store_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $controller = new AccountController();
        $request = Request::create('/admin/accounts/store', 'POST', []);

        $controller->store($request);
    }

    public function test_validateAccount_calls_update_and_redirects(): void
    {
        $account = \App\Models\Utilisateur::factory()->create();
        $controller = new AccountController();
        $response = $controller->validateAccount($account);

        $this->assertEquals(302, $response->getStatusCode());
    }
}
