<?php

namespace Tests\Feature;

use App\Http\Controllers\PresenceController;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PresenceControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_classe_a_les_methodes_attendues(): void
    {
        // given
        // no setup required

        // when
        $hasClasses = method_exists(PresenceController::class, 'classes');
        $hasStudents = method_exists(PresenceController::class, 'students');
        $hasStatus = method_exists(PresenceController::class, 'status');
        $hasSave = method_exists(PresenceController::class, 'save');

        // then
        $this->assertTrue($hasClasses);
        $this->assertTrue($hasStudents);
        $this->assertTrue($hasStatus);
        $this->assertTrue($hasSave);
    }

    public function test_eleves_sans_classe_retourne_tableau_vide(): void
    {
        // given
        $controller = new PresenceController();
        $request = Request::create('/students', 'GET');

        // when
        $response = $controller->students($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    public function test_status_sans_param_retourne_presentIds_vide(): void
    {
        // given
        $controller = new PresenceController();
        $request = Request::create('/status', 'GET');

        // when
        $response = $controller->status($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertArrayHasKey('presentIds', $data);
        $this->assertEquals([], $data['presentIds']);
    }

    public function test_enregistrement_champs_manquants_lance_exception_validation(): void
    {
        $this->expectException(ValidationException::class);
        // given
        $controller = new PresenceController();
        $request = Request::create('/save', 'POST', []);

        // when / then
        $controller->save($request);
    }
}
