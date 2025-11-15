<?php

namespace Tests\Feature;

use App\Http\Controllers\PresenceController;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PresenceControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_class_has_methods(): void
    {
        $this->assertTrue(method_exists(PresenceController::class, 'classes'));
        $this->assertTrue(method_exists(PresenceController::class, 'students'));
        $this->assertTrue(method_exists(PresenceController::class, 'status'));
        $this->assertTrue(method_exists(PresenceController::class, 'save'));
    }

    public function test_students_without_classe_id_returns_empty_array(): void
    {
        $controller = new PresenceController();
        $request = Request::create('/students', 'GET');

        $response = $controller->students($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    public function test_status_without_params_returns_presentIds_empty(): void
    {
        $controller = new PresenceController();
        $request = Request::create('/status', 'GET');

        $response = $controller->status($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertArrayHasKey('presentIds', $data);
        $this->assertEquals([], $data['presentIds']);
    }

    public function test_save_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $controller = new PresenceController();
        $request = Request::create('/save', 'POST', []);

        $controller->save($request);
    }
}
