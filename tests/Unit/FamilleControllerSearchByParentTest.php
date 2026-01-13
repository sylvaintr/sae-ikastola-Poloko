<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class FamilleControllerSearchByParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_recherche_par_parent_retourne_message_quand_aucune_famille_trouvee()
    {
        $request = Request::create('/','GET', ['q' => 'zzzz']);

        $controller = new \App\Http\Controllers\FamilleController();
        $response = $controller->searchByParent($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'Aucune famille trouvée'], $response->getData(true));
    }
}
