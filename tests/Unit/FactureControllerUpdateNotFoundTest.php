<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class FactureControllerUpdateNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_redirige_avec_erreur_si_facture_introuvable()
    {
        // given
        // none

        // when

        // then
        $request = Request::create('/', 'POST');

        $controller = new \App\Http\Controllers\FactureController();
        $response = $controller->update($request, 'non-existent-id');

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $session = $response->getSession();
        $this->assertNotNull($session, 'Expected session on RedirectResponse');
        $this->assertEquals('facture.inexistante', $session->get('error'));
    }
}
