<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Classe;
use App\Http\Controllers\ClasseController;

class ClasseControllerDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_returns_json_response()
    {
        Classe::factory()->count(3)->create();

        $ctrl = new ClasseController();
        $resp = $ctrl->data();

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
        $content = $resp->getContent();
        $this->assertStringContainsString('data', $content);
    }
}
