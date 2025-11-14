<?php

namespace Tests\Feature;

use App\Http\Controllers\FamilleController;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FamilleControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_class_has_public_methods(): void
    {
        $this->assertTrue(method_exists(FamilleController::class, 'ajouter'));
        $this->assertTrue(method_exists(FamilleController::class, 'show'));
        $this->assertTrue(method_exists(FamilleController::class, 'index'));
        $this->assertTrue(method_exists(FamilleController::class, 'delete'));
        $this->assertTrue(method_exists(FamilleController::class, 'update'));
    }

    public function test_show_not_found_returns_404(): void
    {
        // Use an unlikely ID to avoid mocking; assume this ID does not exist in test DB
        $id = 999999;

        $controller = new FamilleController();

        $response = $controller->show($id);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_index_returns_array(): void
    {
        $controller = new FamilleController();
        $response = $controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->getData(true));
    }
}
