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
        // given
        Role::factory()->count(2)->create();
        $controller = new ObligatoryDocumentController();

        // when
        $response = $controller->create();

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
    }

    public function test_edit_returns_view_with_document(): void
    {
        // given
        $doc = \App\Models\DocumentObligatoire::factory()->create();
        $controller = new ObligatoryDocumentController();

        // when
        $resp = $controller->edit($doc);

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $resp);
    }
}
