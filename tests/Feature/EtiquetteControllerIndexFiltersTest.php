<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Etiquette;
use App\Models\Role;
use App\Http\Controllers\EtiquetteController;
use Illuminate\Http\Request;

class EtiquetteControllerIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_applies_search_filter()
    {
        Etiquette::create(['nom' => 'Alpha']);
        Etiquette::create(['nom' => 'Beta']);

        $ctrl = new EtiquetteController();
        $view = $ctrl->index(new Request(['search' => 'Alpha']));

        $etiquettes = $view->getData()['etiquettes'];
        $this->assertCount(1, $etiquettes);
        $this->assertEquals('Alpha', $etiquettes->first()->nom);
    }

    public function test_index_applies_role_filter()
    {
        $role = Role::create(['name' => 'R1']);

        $e1 = Etiquette::create(['nom' => 'WithRole']);
        $e2 = Etiquette::create(['nom' => 'WithoutRole']);

        $e1->roles()->attach($role->idRole);

        $ctrl = new EtiquetteController();
        $view = $ctrl->index(new Request(['role' => $role->idRole]));

        $etiquettes = $view->getData()['etiquettes'];
        $this->assertCount(1, $etiquettes);
        $this->assertEquals('WithRole', $etiquettes->first()->nom);
    }
}
