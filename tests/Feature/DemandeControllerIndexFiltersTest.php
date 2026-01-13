<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Tache;

class DemandeControllerIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_respects_search_etat_type_urgence_filters()
    {
        $this->withoutMiddleware();

        // Matching demande
        $match = Tache::factory()->create([
            'titre' => 'FindMeNow',
            'etat' => 'En attente',
            'type' => 'Réparation',
            'urgence' => 'Faible',
            'dateD' => '2020-01-01'
        ]);

        // Non-matching demande
        $other = Tache::factory()->create([
            'titre' => 'Other',
            'etat' => 'En cours',
            'type' => 'Ménage',
            'urgence' => 'Élevée',
            'dateD' => '2020-01-01'
        ]);

        $request = Request::create('/', 'GET', [
            'search' => 'FindMe',
            'etat' => 'En attente',
            'type' => 'Réparation',
            'urgence' => 'Faible',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $demandes = $resp->getData()['demandes'];

        $ids = collect($demandes->items())->pluck('idTache')->all();
        $this->assertContains($match->idTache, $ids);
        $this->assertNotContains($other->idTache, $ids);
    }

    public function test_index_respects_date_from_and_date_to()
    {
        $this->withoutMiddleware();

        $t1 = Tache::factory()->create(['dateD' => '2020-01-01']);
        $t2 = Tache::factory()->create(['dateD' => '2021-07-01']);

        $request = Request::create('/', 'GET', [
            'date_from' => '2021-01-01',
            'date_to' => '2021-12-31',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);
        $demandes = $resp->getData()['demandes'];

        $ids = collect($demandes->items())->pluck('idTache')->all();
        $this->assertContains($t2->idTache, $ids);
        $this->assertNotContains($t1->idTache, $ids);
    }

    public function test_index_sort_and_direction_apply_correctly()
    {
        $this->withoutMiddleware();

        $a = Tache::factory()->create(['titre' => 'B']);
        $b = Tache::factory()->create(['titre' => 'A']);
        $c = Tache::factory()->create(['titre' => 'C']);

        $request = Request::create('/', 'GET', [
            'sort' => 'title', // maps to 'titre'
            'direction' => 'asc',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);
        $demandes = $resp->getData()['demandes'];

        $names = collect($demandes->items())->pluck('titre')->all();

        // The first three names should be sorted ascending
        $firstThree = array_slice($names, 0, 3);
        $this->assertEquals(['A', 'B', 'C'], $firstThree);
    }
}
