<?php

namespace Tests\Feature;

use App\Http\Controllers\UtilisateurController;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UtilisateurControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_classe_possede_methode_searchByNom(): void
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\UtilisateurController::class, 'searchByNom'));
    }

    public function test_searchByNom_sans_nom_retourne_400(): void
    {
        $controller = new UtilisateurController();
        $request = Request::create('/search', 'GET');

        $response = $controller->searchByNom($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_searchByNom_sans_resultats_retourne_404(): void
    {
        // Use a very unlikely name to avoid needing mocks
        $name = 'no_such_name_' . uniqid();

        $controller = new UtilisateurController();
        $request = Request::create('/search', 'GET', ['nom' => $name]);

        $response = $controller->searchByNom($request);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
