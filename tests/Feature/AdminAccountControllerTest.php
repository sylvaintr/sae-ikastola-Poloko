<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountController;
use Tests\TestCase;

class AdminAccountControllerTest extends TestCase
{
    public function test_class_has_expected_methods(): void
    {
        // given
        // no setup required

        // when
        $hasIndex = method_exists(AccountController::class, 'index');
        $hasCreate = method_exists(AccountController::class, 'create');
        $hasStore = method_exists(AccountController::class, 'store');
        $hasShow = method_exists(AccountController::class, 'show');
        $hasEdit = method_exists(AccountController::class, 'edit');
        $hasUpdate = method_exists(AccountController::class, 'update');
        $hasValidate = method_exists(AccountController::class, 'validateAccount');
        $hasDestroy = method_exists(AccountController::class, 'destroy');

        // then
        $this->assertTrue($hasIndex);
        $this->assertTrue($hasCreate);
        $this->assertTrue($hasStore);
        $this->assertTrue($hasShow);
        $this->assertTrue($hasEdit);
        $this->assertTrue($hasUpdate);
        $this->assertTrue($hasValidate);
        $this->assertTrue($hasDestroy);
    }
}
