<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\StoreActualiteRequest;

class StoreActualiteRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepare_pour_validation_convertit_date_avec_slash_et_definit_archive_true()
    {
        $req = new StoreActualiteRequest();
        // Simulate incoming request data
        $req->merge([
            'dateP' => '25/12/2025',
            'archive' => 'on',
        ]);

        $r = new \ReflectionClass($req);
        $m = $r->getMethod('prepareForValidation');
        $m->setAccessible(true);
        $m->invoke($req);

        $this->assertEquals('2025-12-25', $req->get('dateP'));
        $this->assertTrue($req->get('archive'));
    }

    public function test_prepare_pour_validation_gere_date_manquante_ou_invalide_et_archive_false()
    {
        $req = new StoreActualiteRequest();
        $req->merge([
            'dateP' => '',
            // archive not present
        ]);

        $r = new \ReflectionClass($req);
        $m = $r->getMethod('prepareForValidation');
        $m->setAccessible(true);
        $m->invoke($req);

        $this->assertFalse((bool)$req->get('archive'));
        $this->assertEquals('', $req->get('dateP'));
    }

    public function test_authorize_retourne_true_pour_authentifie_et_false_pour_invite()
    {
        // Guest should be unauthorized
        $req = new StoreActualiteRequest();
        $this->assertFalse($req->authorize());

        // Authenticated should be authorized
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);
        $req2 = new StoreActualiteRequest();
        $this->assertTrue($req2->authorize());
    }
}
