<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class FamilleControllerSearchUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_utilisateurs_retourne_tableau_vide_pour_requete_courte()
    {
        // given
        // No 'q' parameter to trigger the early-return branch (nullable => null)
        $request = Request::create('/', 'GET');

        // when
        $controller = new \App\Http\Controllers\FamilleController();
        $response = $controller->searchUsers($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }
}
