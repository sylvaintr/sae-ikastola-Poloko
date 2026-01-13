<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class FamilleControllerSearchUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_searchUsers_returns_empty_array_for_short_query()
    {
        // No 'q' parameter to trigger the early-return branch (nullable => null)
        $request = Request::create('/', 'GET');

        $controller = new \App\Http\Controllers\FamilleController();
        $response = $controller->searchUsers($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }
}
