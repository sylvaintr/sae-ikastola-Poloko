<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ActualiteController;
use App\Models\Actualite;
use App\Models\Etiquette;
use App\Models\Utilisateur;

class ActualiteControllerMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_convertit_date_avec_slash_et_enregistre_avec_request_simple()
    {
        // given
        // none

        // when

        // then
        Storage::fake('public');

        $user = Utilisateur::factory()->create();
        auth()->login($user);

        $et = Etiquette::factory()->create();

        $file = UploadedFile::fake()->image('pic.jpg');

        $params = [
            'type' => 'public',
            'dateP' => '10/12/2025',
            'titrefr' => 'Titre FR',
            'descriptionfr' => 'Desc FR',
            'descriptioneus' => 'Desc EUS',
            'contenufr' => 'Contenu FR',
            'contenueus' => 'Contenu EUS',
            'etiquettes' => [$et->idEtiquette],
        ];

        $request = Request::create('/actualites', 'POST', $params, [], ['images' => [$file]]);

        $controller = new ActualiteController();
        $controller->store($request);

        $this->assertDatabaseHas('actualite', ['titrefr' => 'Titre FR']);
        $this->assertDatabaseHas('document', ['nom' => 'pic.jpg']);
        $act = Actualite::where('titrefr', 'Titre FR')->first();
        $this->assertGreaterThan(0, $act->documents()->count());
    }

    public function test_data_filters_retournent_json_et_respectent_filtres()
    {
        // given
        // none

        // when

        // then
        $et = Etiquette::factory()->create();
        Actualite::factory()->create(['type' => 'public', 'archive' => false, 'titrefr' => 'A1', 'dateP' => now()])->etiquettes()->attach($et->idEtiquette);
        Actualite::factory()->create(['type' => 'public', 'archive' => true, 'titrefr' => 'A2', 'dateP' => now()]);

        $controller = new ActualiteController();

        $respActive = $controller->data(Request::create('/data', 'GET', ['etat' => 'active']));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $respActive);
        $payload = $respActive->getData(true);
        $this->assertArrayHasKey('data', $payload);

        $respArchived = $controller->data(Request::create('/data', 'GET', ['etat' => 'archived']));
        $p2 = $respArchived->getData(true);
        $this->assertArrayHasKey('data', $p2);

        $respEtiq = $controller->data(Request::create('/data', 'GET', ['etiquette' => [$et->idEtiquette]]));
        $p3 = $respEtiq->getData(true);
        $this->assertArrayHasKey('data', $p3);
    }

    public function test_appel_delegue_aux_helpers()
    {
        // given
        // none

        // when

        // then
        $act = Actualite::factory()->create(['titrefr' => 'CT']);
        $controller = new ActualiteController();

        // columnTitre is implemented in ActualiteHelpers and should be accessible via __call
        $res = $controller->columnTitre($act);
        $this->assertStringContainsString('CT', $res);

        // columnActionsHtml should return a View
        $view = $controller->columnActionsHtml($act);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }
}
