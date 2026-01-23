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
        // given
        // none

        // when
        $hasIndex = method_exists(ObligatoryDocumentController::class, 'index');
        $hasCreate = method_exists(ObligatoryDocumentController::class, 'create');
        $hasStore = method_exists(ObligatoryDocumentController::class, 'store');
        $hasEdit = method_exists(ObligatoryDocumentController::class, 'edit');
        $hasUpdate = method_exists(ObligatoryDocumentController::class, 'update');
        $hasDestroy = method_exists(ObligatoryDocumentController::class, 'destroy');

        // then
        $this->assertTrue($hasIndex);
        $this->assertTrue($hasCreate);
        $this->assertTrue($hasStore);
        $this->assertTrue($hasEdit);
        $this->assertTrue($hasUpdate);
        $this->assertTrue($hasDestroy);
    }

    public function test_store_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);
        // given
        $controller = new ObligatoryDocumentController();
        $request = Request::create('/admin/obligatory-documents/store', 'POST', []);

        // when / then
        $controller->store($request);
    }


    public function test_getNomMaxLength_returns_default_on_db_exception(): void
    {
        // given
        // Force DB to throw so getNomMaxLength catches and returns default
        DB::shouldReceive('table')->andThrow(new \Exception('boom'));

        $controller = new ObligatoryDocumentController();

        // when
        $view = $controller->create();

        // then
        $data = $view->getData();
        $this->assertArrayHasKey('nomMaxLength', $data);
        $this->assertEquals(100, $data['nomMaxLength']);
    }
    
}
