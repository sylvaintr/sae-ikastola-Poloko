<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Tache;
use App\Models\Evenement;

class DemandeControllerEvenementFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_evenement()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create();

        $demandeWithEvent = Tache::factory()->create([
            'titre' => 'Demande liée',
            'idEvenement' => $evenement->idEvenement,
        ]);

        $demandeWithoutEvent = Tache::factory()->create([
            'titre' => 'Demande sans événement',
            'idEvenement' => null,
        ]);

        // when
        $request = Request::create('/', 'GET', [
            'evenement' => $evenement->idEvenement,
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);

        // then
        $demandes = $resp->getData()['demandes'];
        $ids = collect($demandes->items())->pluck('idTache')->all();

        $this->assertContains($demandeWithEvent->idTache, $ids);
        $this->assertNotContains($demandeWithoutEvent->idTache, $ids);
    }

    public function test_index_filters_by_no_evenement()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create();

        $demandeWithEvent = Tache::factory()->create([
            'titre' => 'Demande liée',
            'idEvenement' => $evenement->idEvenement,
        ]);

        $demandeWithoutEvent = Tache::factory()->create([
            'titre' => 'Demande sans événement',
            'idEvenement' => null,
        ]);

        // when
        $request = Request::create('/', 'GET', [
            'evenement' => 'none',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);

        // then
        $demandes = $resp->getData()['demandes'];
        $ids = collect($demandes->items())->pluck('idTache')->all();

        $this->assertContains($demandeWithoutEvent->idTache, $ids);
        $this->assertNotContains($demandeWithEvent->idTache, $ids);
    }

    public function test_index_shows_all_when_evenement_filter_is_all()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create();

        $demandeWithEvent = Tache::factory()->create([
            'idEvenement' => $evenement->idEvenement,
        ]);

        $demandeWithoutEvent = Tache::factory()->create([
            'idEvenement' => null,
        ]);

        // when
        $request = Request::create('/', 'GET', [
            'evenement' => 'all',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);

        // then
        $demandes = $resp->getData()['demandes'];
        $ids = collect($demandes->items())->pluck('idTache')->all();

        $this->assertContains($demandeWithEvent->idTache, $ids);
        $this->assertContains($demandeWithoutEvent->idTache, $ids);
    }

    public function test_index_passes_evenements_list_to_view()
    {
        // given
        $this->withoutMiddleware();

        $initialCount = Evenement::count();
        Evenement::factory()->count(3)->create();

        // when
        $request = Request::create('/', 'GET');
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);

        // then
        $viewData = $resp->getData();
        $this->assertArrayHasKey('evenements', $viewData);
        $this->assertCount($initialCount + 3, $viewData['evenements']);
    }

    public function test_export_respects_evenement_filter()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create();

        $demandeWithEvent = Tache::factory()->create([
            'titre' => 'Demande Export Test',
            'idEvenement' => $evenement->idEvenement,
        ]);

        $demandeWithoutEvent = Tache::factory()->create([
            'titre' => 'Autre Demande',
            'idEvenement' => null,
        ]);

        // when
        $request = Request::create('/', 'GET', [
            'evenement' => $evenement->idEvenement,
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $response = $controller->export($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('Demande Export Test', $content);
        $this->assertStringNotContainsString('Autre Demande', $content);
    }
}
