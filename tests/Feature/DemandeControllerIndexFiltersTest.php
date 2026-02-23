<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Tache;

class DemandeControllerIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_respects_search_etat_urgence_filters()
    {
        // given
        $this->withoutMiddleware();
        // Matching demande - type='demande' is the discriminator
        $match = Tache::factory()->create([
            'titre' => 'FindMeNow',
            'etat' => 'En attente',
            'type' => 'demande',
            'urgence' => 'Faible',
            'dateD' => '2020-01-01'
        ]);

        // Non-matching demande (different etat and urgence)
        $other = Tache::factory()->create([
            'titre' => 'Other',
            'etat' => 'En cours',
            'type' => 'demande',
            'urgence' => 'Élevée',
            'dateD' => '2020-01-01'
        ]);

        // when
        $request = Request::create('/', 'GET', [
            'search' => 'FindMe',
            'etat' => 'En attente',
            'urgence' => 'Faible',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);

        // then
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $demandes = $resp->getData()['demandes'];

        $ids = collect($demandes->items())->pluck('idTache')->all();
        $this->assertContains($match->idTache, $ids);
        $this->assertNotContains($other->idTache, $ids);
    }

    public function test_index_respects_date_from_and_date_to()
    {
        // given
        $this->withoutMiddleware();
        $t1 = Tache::factory()->create(['dateD' => '2020-01-01']);
        $t2 = Tache::factory()->create(['dateD' => '2021-07-01']);

        // when
        $request = Request::create('/', 'GET', [
            'date_from' => '2021-01-01',
            'date_to' => '2021-12-31',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);
        $demandes = $resp->getData()['demandes'];

        // then
        $ids = collect($demandes->items())->pluck('idTache')->all();
        $this->assertContains($t2->idTache, $ids);
        $this->assertNotContains($t1->idTache, $ids);
    }

    public function test_index_sort_and_direction_apply_correctly()
    {
        // given
        $this->withoutMiddleware();
        $a = Tache::factory()->create(['titre' => 'B']);
        $b = Tache::factory()->create(['titre' => 'A']);
        $c = Tache::factory()->create(['titre' => 'C']);

        // when
        $request = Request::create('/', 'GET', [
            'sort' => 'title', // maps to 'titre'
            'direction' => 'asc',
        ]);

        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->index($request);
        $demandes = $resp->getData()['demandes'];

        // then
        $names = collect($demandes->items())->pluck('titre')->all();
        // The first three names should be sorted ascending
        $firstThree = array_slice($names, 0, 3);
        $this->assertEquals(['A', 'B', 'C'], $firstThree);
    }
}
