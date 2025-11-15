<?php

namespace Tests\Feature;

use App\Http\Controllers\LierController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LierControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_class_has_updateParite_method(): void
    {
        $this->assertTrue(method_exists(LierController::class, 'updateParite'));
    }

    public function test_updateParite_with_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $controller = new LierController();
        $request = Request::create('/update-parite', 'POST', []);

        $controller->updateParite($request);
    }
}
