<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Etiquette;
use App\Http\Controllers\EtiquetteController;

class EtiquetteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_methodes_colonne_retournent_valeurs_attendues()
    {
        $etiquette = Etiquette::factory()->create(['nom' => 'Tag1']);

        $controller = new EtiquetteController();

        $this->assertEquals($etiquette->idEtiquette, $controller->columnIdEtiquette($etiquette));
        $this->assertEquals('Tag1', $controller->columnNom($etiquette));
        $this->assertIsString($controller->columnRolesText($etiquette));

        // Ensure the filter callback can be invoked without throwing
        $query = Etiquette::query();
        $controller->filterColumnRolesCallback($query, 'foo');
        $this->assertTrue(true);
    }
}
