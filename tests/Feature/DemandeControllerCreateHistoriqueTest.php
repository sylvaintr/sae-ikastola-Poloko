<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Tache;

class DemandeControllerCreateHistoriqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_createHistorique_redirects_when_demande_terminated()
    {
        // given
        $this->withoutMiddleware();
        $demande = Tache::factory()->create(['etat' => 'Terminé']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->createHistorique($demande);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_createHistorique_returns_view_when_not_terminated()
    {
        // given
        $this->withoutMiddleware();
        $demande = Tache::factory()->create(['etat' => 'En attente']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->createHistorique($demande);

        // then
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $this->assertArrayHasKey('demande', $resp->getData());
    }

    public function test_update_redirects_when_demande_terminated()
    {
        // given
        $this->withoutMiddleware();
        $demande = Tache::factory()->create(['etat' => 'Terminé']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $request = \Illuminate\Http\Request::create('/', 'PUT', [
            'titre' => 'Ignored',
            'description' => 'Ignored',
            'type' => 'Réparation',
            'urgence' => 'Faible',
        ]);

        $resp = $controller->update($request, $demande);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_edit_redirects_when_demande_terminated()
    {
        // given
        $this->withoutMiddleware();
        $demande = Tache::factory()->create(['etat' => 'Terminé']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->edit($demande);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_edit_returns_view_with_types_and_urgences_when_not_terminated()
    {
        // given
        $this->withoutMiddleware();
        // create some existing types so the types collection is not empty
        Tache::factory()->create(['type' => 'Réparation']);
        Tache::factory()->create(['type' => 'Ménage']);
        $demande = Tache::factory()->create(['etat' => 'En attente']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->edit($demande);

        // then
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $data = $resp->getData();

        $this->assertArrayHasKey('types', $data);
        $this->assertArrayHasKey('urgences', $data);
        $this->assertArrayHasKey('demande', $data);

        $this->assertNotEmpty($data['types']);
        $this->assertEquals(['Faible', 'Moyenne', 'Élevée'], $data['urgences']);
    }

    public function test_edit_uses_default_types_when_none_exist()
    {
        // given
        $this->withoutMiddleware();
        // create a demande with empty type so the distinct types collection is empty after filter()
        $demande = Tache::factory()->create(['type' => '', 'etat' => 'En attente']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->edit($demande);

        // then
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $data = $resp->getData();

        $this->assertArrayHasKey('types', $data);
        $this->assertEquals(['Réparation', 'Ménage', 'Maintenance'], $data['types']->values()->all());
    }

    public function test_storeHistorique_redirects_when_demande_terminated()
    {
        // given
        $this->withoutMiddleware();
        $demande = Tache::factory()->create(['etat' => 'Terminé']);

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'titre' => 'Some title',
            'description' => 'Some description',
            'depense' => 10,
        ]);

        $resp = $controller->storeHistorique($request, $demande);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }
}
