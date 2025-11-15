<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountController;
use Tests\TestCase;

class AdminAccountControllerTest extends TestCase
{
    public function test_class_has_expected_methods(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'index'));
        $this->assertTrue(method_exists(AccountController::class, 'create'));
        $this->assertTrue(method_exists(AccountController::class, 'store'));
        $this->assertTrue(method_exists(AccountController::class, 'show'));
        $this->assertTrue(method_exists(AccountController::class, 'edit'));
        $this->assertTrue(method_exists(AccountController::class, 'update'));
        $this->assertTrue(method_exists(AccountController::class, 'validateAccount'));
        $this->assertTrue(method_exists(AccountController::class, 'destroy'));
    }
}
