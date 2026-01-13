<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class FamilleControllerSearchByParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_searchByParent_returns_message_when_no_families_found()
    {
        $request = Request::create('/','GET', ['q' => 'zzzz']);

        $controller = new \App\Http\Controllers\FamilleController();
        $response = $controller->searchByParent($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'Aucune famille trouvée'], $response->getData(true));
    }
}
