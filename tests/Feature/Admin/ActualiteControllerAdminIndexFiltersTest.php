<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Actualite;
use App\Models\Etiquette;

class ActualiteControllerAdminIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_adminIndex_filters_by_type()
    {
        $this->withoutMiddleware();

        $a = Actualite::factory()->create(['type' => 'news']);
        $b = Actualite::factory()->create(['type' => 'event']);

        $request = Request::create('/', 'GET', ['type' => 'news']);
        $controller = new \App\Http\Controllers\ActualiteController();
        $resp = $controller->adminIndex($request);

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $data = $resp->getData();
        $this->assertArrayHasKey('actualites', $data);

        $ids = collect($data['actualites']->items())->pluck('idActualite')->all();
        $this->assertContains($a->idActualite, $ids);
        $this->assertNotContains($b->idActualite, $ids);
    }

    public function test_adminIndex_filters_by_etat_active_and_archive()
    {
        $this->withoutMiddleware();

        $active = Actualite::factory()->create(['archive' => false]);
        $archived = Actualite::factory()->create(['archive' => true]);

        $reqActive = Request::create('/', 'GET', ['etat' => 'active']);
        $respA = (new \App\Http\Controllers\ActualiteController())->adminIndex($reqActive);
        $idsA = collect($respA->getData()['actualites']->items())->pluck('idActualite')->all();
        $this->assertContains($active->idActualite, $idsA);
        $this->assertNotContains($archived->idActualite, $idsA);

        $reqArch = Request::create('/', 'GET', ['etat' => 'archived']);
        $respB = (new \App\Http\Controllers\ActualiteController())->adminIndex($reqArch);
        $idsB = collect($respB->getData()['actualites']->items())->pluck('idActualite')->all();
        $this->assertContains($archived->idActualite, $idsB);
        $this->assertNotContains($active->idActualite, $idsB);
    }

    public function test_adminIndex_filters_by_etiquette()
    {
        $this->withoutMiddleware();

        $et = Etiquette::factory()->create();
        $a = Actualite::factory()->create();
        $b = Actualite::factory()->create();

        // attach etiquette to $a via pivot 'correspondre'
        $a->etiquettes()->attach($et->idEtiquette);

        $req = Request::create('/', 'GET', ['etiquette' => [$et->idEtiquette]]);
        $resp = (new \App\Http\Controllers\ActualiteController())->adminIndex($req);
        $ids = collect($resp->getData()['actualites']->items())->pluck('idActualite')->all();
        $this->assertContains($a->idActualite, $ids);
        $this->assertNotContains($b->idActualite, $ids);
    }

    public function test_adminIndex_filters_by_search()
    {
        $this->withoutMiddleware();

        $match = Actualite::factory()->create(['titrefr' => 'UniqueTitleHere']);
        Actualite::factory()->create(['titrefr' => 'Other']);

        $req = Request::create('/', 'GET', ['search' => 'UniqueTitleHere']);
        $resp = (new \App\Http\Controllers\ActualiteController())->adminIndex($req);
        $ids = collect($resp->getData()['actualites']->items())->pluck('idActualite')->all();
        $this->assertContains($match->idActualite, $ids);
    }
}
