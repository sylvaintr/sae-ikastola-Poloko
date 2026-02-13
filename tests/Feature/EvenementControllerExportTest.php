<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Evenement;
use App\Models\Recette;
use App\Models\Role;

class EvenementControllerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_returns_csv_response()
    {
        // given
        $this->withoutMiddleware();

        Evenement::factory()->count(2)->create();

        // when
        $request = Request::create('/', 'GET');
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->export($request);

        // then
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
    }

    public function test_export_includes_evenement_data()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create([
            'titre' => 'Test Export Evenement',
            'obligatoire' => true,
        ]);

        // when
        $request = Request::create('/', 'GET');
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->export($request);

        // then
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Test Export Evenement', $content);
    }

    public function test_export_respects_search_filter()
    {
        // given
        $this->withoutMiddleware();

        $matchingEvent = Evenement::factory()->create([
            'titre' => 'Fete de Noel',
        ]);

        $otherEvent = Evenement::factory()->create([
            'titre' => 'Reunion Parents',
        ]);

        // when
        $request = Request::create('/', 'GET', [
            'search' => 'Noel',
        ]);
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->export($request);

        // then
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Fete de Noel', $content);
        $this->assertStringNotContainsString('Reunion Parents', $content);
    }

    public function test_export_calculates_totals_correctly()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create([
            'titre' => 'Evenement Comptabilite',
        ]);

        Recette::factory()->create([
            'idEvenement' => $evenement->idEvenement,
            'type' => 'recette',
            'prix' => 100.00,
            'quantite' => 2,
        ]);

        Recette::factory()->create([
            'idEvenement' => $evenement->idEvenement,
            'type' => 'depense',
            'prix' => 50.00,
            'quantite' => 1,
        ]);

        // when
        $request = Request::create('/', 'GET');
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->export($request);

        // then
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Evenement Comptabilite', $content);
        // Total recettes: 100 * 2 = 200
        $this->assertStringContainsString('200,00', $content);
        // Total depenses: 50 * 1 = 50
        $this->assertStringContainsString('50,00', $content);
    }

    public function test_export_csv_single_evenement_returns_csv()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create([
            'titre' => 'Export Single Test',
        ]);

        // when
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->exportCsv($evenement);

        // then
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_export_csv_single_includes_recettes()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create([
            'titre' => 'Evenement Avec Recettes',
        ]);

        Recette::factory()->create([
            'idEvenement' => $evenement->idEvenement,
            'type' => 'recette',
            'description' => 'Vente billets',
            'prix' => 15.00,
            'quantite' => 10,
        ]);

        Recette::factory()->create([
            'idEvenement' => $evenement->idEvenement,
            'type' => 'depense_previsionnelle',
            'description' => 'Location salle',
            'prix' => 200.00,
            'quantite' => 1,
        ]);

        // when
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->exportCsv($evenement);

        // then
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Evenement Avec Recettes', $content);
        $this->assertStringContainsString('Vente billets', $content);
        $this->assertStringContainsString('Location salle', $content);
        // Total recettes: 15 * 10 = 150
        $this->assertStringContainsString('150,00', $content);
        // Total depenses prev: 200 * 1 = 200
        $this->assertStringContainsString('200,00', $content);
    }

    public function test_export_csv_filename_contains_evenement_title()
    {
        // given
        $this->withoutMiddleware();

        $evenement = Evenement::factory()->create([
            'titre' => 'Mon Super Evenement',
        ]);

        // when
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->exportCsv($evenement);

        // then
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Mon_Super_Evenement', $contentDisposition);
        $this->assertStringContainsString('_evenement_', $contentDisposition);
    }

    public function test_export_includes_roles()
    {
        // given
        $this->withoutMiddleware();

        $role = Role::factory()->create(['name' => 'Commission Fetes']);

        $evenement = Evenement::factory()->create([
            'titre' => 'Evenement Avec Roles',
        ]);
        $evenement->roles()->attach($role->idRole);

        // when
        $controller = new \App\Http\Controllers\EvenementController();
        $response = $controller->exportCsv($evenement);

        // then
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Commission Fetes', $content);
    }
}
