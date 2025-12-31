<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ObligatoryDocumentController;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObligatoryDocumentControllerViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_returns_view_with_roles(): void
    {
        Role::factory()->count(2)->create();
        $controller = new ObligatoryDocumentController();
        $response = $controller->create();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
    }

    public function test_edit_returns_view_with_document(): void
    {
        $doc = \App\Models\DocumentObligatoire::factory()->create();
        $controller = new ObligatoryDocumentController();
        $resp = $controller->edit($doc);

        $this->assertInstanceOf(\Illuminate\View\View::class, $resp);
    }
}
