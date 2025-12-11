<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ObligatoryDocumentController;
use App\Models\DocumentObligatoire;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ObligatoryDocumentControllerFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_with_delai_creates_document_and_syncs_roles(): void
    {
        $role = Role::factory()->create();

        $controller = new ObligatoryDocumentController();

        $request = Request::create('/admin/obligatory-documents/store', 'POST', [
            'nom' => 'DocTest',
            'expirationType' => 'delai',
            'delai' => 30,
            'roles' => [$role->idRole],
        ]);

        $response = $controller->store($request);

        $this->assertEquals(302, $response->getStatusCode());

        $this->assertDatabaseHas('documentObligatoire', ['nom' => 'DocTest']);
        $doc = DocumentObligatoire::where('nom', 'DocTest')->first();
        $this->assertNotNull($doc->delai);
    }

    public function test_update_and_destroy_work(): void
    {
        $role = Role::factory()->create();
        $doc = DocumentObligatoire::factory()->create();
        $controller = new ObligatoryDocumentController();

        $request = Request::create('/admin/obligatory-documents/update', 'PUT', [
            'nom' => 'UpdatedName',
            'expirationType' => 'none',
            'roles' => [$role->idRole],
        ]);

        $response = $controller->update($request, $doc);
        $this->assertEquals(302, $response->getStatusCode());

        $this->assertDatabaseHas('documentObligatoire', ['idDocumentObligatoire' => $doc->idDocumentObligatoire, 'nom' => 'UpdatedName']);

        $resp2 = $controller->destroy($doc);
        $this->assertEquals(302, $resp2->getStatusCode());
        $this->assertDatabaseMissing('documentObligatoire', ['idDocumentObligatoire' => $doc->idDocumentObligatoire]);
    }
}
