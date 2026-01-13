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

    public function test_applyRoleWhereHas_adds_where_binding()
    {
        $controller = new EtiquetteController();

        $q = Role::query();
        $controller->applyRoleWhereHas($q, 42);

        $this->assertContains(42, $q->getBindings());
    }

    public function test_filterRolesColumnByKeyword_adds_like_bindings()
    {
        $controller = new EtiquetteController();

        $query = Etiquette::query();
        $controller->filterRolesColumnByKeyword($query, 'term');

        $this->assertContains('%term%', $query->getBindings());
    }

    public function test_columnActionsHtml_returns_view_with_etiquette()
    {
        $etiquette = Etiquette::factory()->create(['nom' => 'T']);
        $controller = new EtiquetteController();

        $view = $controller->columnActionsHtml($etiquette);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertArrayHasKey('etiquette', $view->getData());
    }
}
