<?php

namespace Tests\Feature;

use App\Http\Controllers\PresenceController;
use App\Models\Classe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PresenceControllerSelectorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_classes_returns_list(): void
    {
        // given
        Classe::factory()->count(3)->create();

        // when
        $controller = new PresenceController();
        $resp = $controller->classes();

        // then
        $this->assertEquals(200, $resp->getStatusCode());
        $data = $resp->getData(true);
        $this->assertIsArray($data);
    }

    public function test_status_with_no_results_returns_empty_presentIds(): void
    {
        // given
        $controller = new PresenceController();
        $request = Request::create('/presence/status', 'GET', ['classe_id' => 999999, 'date' => now()->format('Y-m-d')]);

        // when
        $resp = $controller->status($request);

        // then
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertArrayHasKey('presentIds', $resp->getData(true));
    }
}
