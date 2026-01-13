<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Etiquette;
use App\Http\Controllers\ActualiteController;

class ActualiteControllerPublicTagIdsEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tag_ids_are_empty_when_schema_has_no_is_public_column()
    {
        $this->withoutMiddleware();

        // Force Schema::hasColumn to be false
        Schema::shouldReceive('hasColumn')->andReturn(false);
        // allow Schema::table() calls that follow in ensureEtiquetteIsPublicColumn
        Schema::shouldReceive('table')->andReturnNull();

        // create some etiquettes that would otherwise be considered
        Etiquette::factory()->count(2)->create();

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $controller = new ActualiteController();
        $resp = $controller->index(null);

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);

        $data = $resp->getData();
        $this->assertArrayHasKey('etiquettes', $data);
        $this->assertEmpty($data['etiquettes']);

        // Ensure no SQL query references the is_public column
        foreach ($queries as $sql) {
            $this->assertStringNotContainsString('is_public', strtolower($sql));
        }
    }
}
