<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ObligatoryDocumentController;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ObligatoryDocumentControllerTest extends TestCase
{
    use RefreshDatabase;
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
}
