<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\ActualiteController;
use App\Models\Role;
use App\Models\Etiquette;
use App\Models\Actualite;

class DataTablesDataCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_etiquette_data_with_role_zero_and_without_role()
    {
        Role::factory()->create();
        $et = Etiquette::factory()->create();

        $controller = new EtiquetteController();

        // role explicitly '0' (filled but falsy) — should not error
        $resp0 = $controller->data(Request::create('/data', 'GET', ['role' => '0']));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp0);
        $payload0 = $resp0->getData(true);
        $this->assertArrayHasKey('data', $payload0);

        // no role parameter at all
        $resp1 = $controller->data(Request::create('/data', 'GET', []));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp1);
        $payload1 = $resp1->getData(true);
        $this->assertArrayHasKey('data', $payload1);
    }

    public function test_actualite_data_with_single_and_array_etiquette_and_etat_filters()
    {
        // create one actualite and one etiquette to exercise queries
        Actualite::factory()->create(['archive' => false, 'titrefr' => 't1']);
        $et = Etiquette::factory()->create();

        $controller = new ActualiteController();

        // single etiquette id
        $respSingle = $controller->data(Request::create('/data', 'GET', ['etiquette' => $et->idEtiquette]));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $respSingle);
        $this->assertArrayHasKey('data', $respSingle->getData(true));

        // etiquette as array
        $respArray = $controller->data(Request::create('/data', 'GET', ['etiquette' => [$et->idEtiquette]]));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $respArray);
        $this->assertArrayHasKey('data', $respArray->getData(true));

        // etat active
        $respActive = $controller->data(Request::create('/data', 'GET', ['etat' => 'active']));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $respActive);

        // etat archived
        $respArchived = $controller->data(Request::create('/data', 'GET', ['etat' => 'archived']));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $respArchived);
    }

    public function test_column_actions_return_view_instances()
    {
        $et = Etiquette::factory()->create();
        $act = Actualite::factory()->create();

        $eController = new EtiquetteController();
        $aController = new ActualiteController();

        $viewE = $eController->columnActionsHtml($et);
        $this->assertInstanceOf(\Illuminate\View\View::class, $viewE);

        $viewA = $aController->columnActionsHtml($act);
        $this->assertInstanceOf(\Illuminate\View\View::class, $viewA);
    }
}
