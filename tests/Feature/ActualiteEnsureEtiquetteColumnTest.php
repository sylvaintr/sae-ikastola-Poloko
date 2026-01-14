<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\Etiquette;
use App\Http\Controllers\ActualiteController;

class ActualiteEnsureEtiquetteColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensureEtiquette_table_called_when_is_public_column_missing()
    {
        // given
        $this->withoutMiddleware();

        // Ensure hasColumn returns false so the controller will try to add the column
        Schema::shouldReceive('hasColumn')->andReturn(false);

        // Expect Schema::table to be called with the 'etiquette' table and a Closure
        Schema::shouldReceive('table')->once()->with('etiquette', \Mockery::type('Closure'));

        // create a dummy etiquette so index can run without failing on empty DB
        Etiquette::factory()->create();

        // when
        $controller = new ActualiteController();
        $resp = $controller->index(null);

        // then
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
    }
}
