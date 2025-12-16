<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ObligatoryDocumentController;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ObligatoryDocumentControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure cached value is reset before feature tests
        $prop = new \ReflectionProperty(\App\Http\Controllers\Admin\ObligatoryDocumentController::class, 'cachedNomMaxLength');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }
    public function test_class_has_expected_methods(): void
    {
        $this->assertTrue(method_exists(ObligatoryDocumentController::class, 'index'));
        $this->assertTrue(method_exists(ObligatoryDocumentController::class, 'create'));
        $this->assertTrue(method_exists(ObligatoryDocumentController::class, 'store'));
        $this->assertTrue(method_exists(ObligatoryDocumentController::class, 'edit'));
        $this->assertTrue(method_exists(ObligatoryDocumentController::class, 'update'));
        $this->assertTrue(method_exists(ObligatoryDocumentController::class, 'destroy'));
    }

    public function test_store_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $controller = new ObligatoryDocumentController();
        $request = Request::create('/admin/obligatory-documents/store', 'POST', []);

        $controller->store($request);
    }


    public function test_getNomMaxLength_returns_default_on_db_exception(): void
    {
        // Force DB to throw so getNomMaxLength catches and returns default
        DB::shouldReceive('table')->andThrow(new \Exception('boom'));

        $controller = new ObligatoryDocumentController();

        // create() calls getNomMaxLength(), which should catch the exception and return the default (100)
        $view = $controller->create();

        $data = $view->getData();
        $this->assertArrayHasKey('nomMaxLength', $data);
        $this->assertEquals(100, $data['nomMaxLength']);
    }
    
}
