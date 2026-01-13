<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\Etiquette;
use App\Http\Controllers\ActualiteController;

class ActualiteControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_uses_empty_public_tags_when_schema_has_no_is_public_column()
    {
        $this->withoutMiddleware();

        // Ensure Schema::hasColumn returns false to exercise the false branch
        Schema::shouldReceive('hasColumn')->andReturn(false);
        // When hasColumn is false the controller will call Schema::table(); allow it
        Schema::shouldReceive('table')->andReturnNull();

        // Create some etiquettes that would normally be public, but should be ignored
        Etiquette::factory()->count(3)->create();

        $controller = new ActualiteController();
        $resp = $controller->index(null);

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);

        $data = $resp->getData();

        // When hasColumn is false, publicTagIds should be [], so etiquettes returned should be empty
        $this->assertArrayHasKey('etiquettes', $data);
        $this->assertEmpty($data['etiquettes']);

        // selectedEtiquettes session key should be cleared for guests
        $this->assertNull(session('selectedEtiquettes'));
    }
}
