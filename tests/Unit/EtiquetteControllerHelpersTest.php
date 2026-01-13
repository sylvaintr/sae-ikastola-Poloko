<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\EtiquetteController;
use App\Models\Etiquette;
use App\Models\Role;

class EtiquetteControllerHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_applyRoleWhereHas_ajoute_clause_where()
    {
        $controller = new EtiquetteController();

        $q = Role::query();
        $controller->applyRoleWhereHas($q, 42);

        $this->assertContains(42, $q->getBindings());
    }

    public function test_filterRolesColumn_par_mot_cle_ajoute_like()
    {
        $controller = new EtiquetteController();

        $query = Etiquette::query();
        $controller->filterRolesColumnByKeyword($query, 'term');

        $this->assertContains('%term%', $query->getBindings());
    }

    public function test_actions_colonne_html_retourne_vue_avec_etiquette()
    {
        $etiquette = Etiquette::factory()->create(['nom' => 'T']);
        $controller = new EtiquetteController();

        $view = $controller->columnActionsHtml($etiquette);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertArrayHasKey('etiquette', $view->getData());
    }
}
