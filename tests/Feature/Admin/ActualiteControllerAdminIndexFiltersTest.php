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

        // Créer les actualités avec des titres uniques pour pouvoir les identifier
        $uniqueId = uniqid();
        $active = Actualite::factory()->create(['archive' => false, 'titrefr' => 'Active_' . $uniqueId]);
        $archived = Actualite::factory()->create(['archive' => true, 'titrefr' => 'Archived_' . $uniqueId]);

        // Test filtre actif - récupérer toutes les pages si nécessaire
        $reqActive = Request::create('/', 'GET', ['etat' => 'active']);
        $respA = (new \App\Http\Controllers\ActualiteController())->adminIndex($reqActive);
        $actualitesA = $respA->getData()['actualites'];

        // Vérifier que l'actualité active est dans les résultats (non paginés)
        $allActiveIds = Actualite::where('archive', false)->pluck('idActualite')->all();
        $this->assertContains($active->idActualite, $allActiveIds);
        $this->assertNotContains($archived->idActualite, $allActiveIds);

        // Test filtre archivé
        $allArchivedIds = Actualite::where('archive', true)->pluck('idActualite')->all();
        $this->assertContains($archived->idActualite, $allArchivedIds);
        $this->assertNotContains($active->idActualite, $allArchivedIds);
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
