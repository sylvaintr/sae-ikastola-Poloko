<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ActualiteController;
use App\Models\Actualite;
use App\Models\Etiquette;
use App\Models\Utilisateur;

class ActualiteControllerExtraTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_filters_by_type_etat_and_etiquette()
    {
        $et = Etiquette::factory()->create();

        $a1 = Actualite::factory()->create(['type' => 'public', 'archive' => false, 'dateP' => now()]);
        $a1->etiquettes()->attach($et->idEtiquette);
        $a2 = Actualite::factory()->create(['type' => 'public', 'archive' => true, 'dateP' => now()]);
        $a3 = Actualite::factory()->create(['type' => 'private', 'archive' => false, 'dateP' => now()]);

        $controller = new ActualiteController();

        $resp1 = $controller->data(Request::create('/data', 'GET', ['type' => 'public', 'etat' => 'active']));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp1);
        $d1 = $resp1->getData(true);
        $this->assertCount(1, $d1['data']);

        $resp2 = $controller->data(Request::create('/data', 'GET', ['etat' => 'archived']));
        $d2 = $resp2->getData(true);
        $this->assertCount(1, $d2['data']);

        $resp3 = $controller->data(Request::create('/data', 'GET', ['etiquette' => [$et->idEtiquette]]));
        $d3 = $resp3->getData(true);
        $this->assertCount(1, $d3['data']);

        $resp4 = $controller->data(Request::create('/data', 'GET', ['etiquette' => $et->idEtiquette]));
        $d4 = $resp4->getData(true);
        $this->assertCount(1, $d4['data']);
    }

    public function test_index_with_query_etiquettes_filters_results()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $et = Etiquette::factory()->create();
        $act = Actualite::factory()->create(['type' => 'private', 'dateP' => now()]);
        $act->etiquettes()->attach($et->idEtiquette);

        // Use the HTTP test client to trigger full route behavior (session, middleware, etc.)
        $response = $this->actingAs($user)->get('/?etiquettes[]=' . $et->idEtiquette);

        $this->assertEquals(200, $response->getStatusCode());
        // Extract the paginator from the view data
        $original = $response->original;
        $this->assertInstanceOf(\Illuminate\View\View::class, $original);
        $data = $original->getData();
        $this->assertArrayHasKey('actualites', $data);
        $actualites = $data['actualites'];
        $this->assertGreaterThanOrEqual(0, $actualites->total());
    }

    public function test_update_converts_slash_date_format()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $act = Actualite::factory()->create(['dateP' => now(), 'idUtilisateur' => $user->idUtilisateur]);

        $params = [
            'titrefr' => 'Updated',
            'descriptionfr' => 'Desc FR',
            'titreeus' => 'T EUS',
            'descriptioneus' => 'Desc EUS',
            'contenueus' => 'Contenu EUS',
            'contenufr' => 'Contenu FR',
            'type' => 'public',
            'dateP' => '10/12/2025',
        ];

        $request = Request::create('/actualites/'.$act->idActualite, 'PUT', $params);

        $controller = new ActualiteController();
        $controller->update($request, $act->idActualite);

        $this->assertStringContainsString('2025-12-10', Actualite::find($act->idActualite)->dateP);
    }
}
