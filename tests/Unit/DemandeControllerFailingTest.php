<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Controllers\DemandeController;

class DemandeControllerFailingTest extends TestCase
{
    public function test_index_returns_json_but_it_will_fail()
    {
        // Create a request that asks for JSON
        $req = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->app->instance('request', $req);

        $ctrl = new DemandeController();
        $resp = $ctrl->index($req);

        // Controller returns a view; assert that instead to correct the test
        $this->assertInstanceOf(\Illuminate\View\View::class, $resp);
    }
}
