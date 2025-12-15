<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\ActualiteController;
use App\Http\Requests\StoreActualiteRequest;
use App\Models\Actualite;
use App\Models\Utilisateur;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\PresenceController;

class CoverageExtras2Test extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_update_with_formrequest_like_object()
    {
        $controller = new ActualiteController();

        $user = \App\Models\Utilisateur::factory()->create();
        auth()->login($user);

        $data = [
            'type' => 'public',
            'dateP' => now()->toDateString(),
            'titrefr' => 't',
            'descriptionfr' => 'd',
            'descriptioneus' => 'de',
            'contenufr' => 'c',
            'contenueus' => 'ce',
            'etiquettes' => [],
        ];

        // Create a Request-like object with a validated() helper
        $orig = Request::create('/', 'POST', $data);
        $mock = new class(
            $orig->query->all(),
            $orig->request->all(),
            $orig->attributes->all(),
            $orig->cookies->all(),
            $orig->files->all(),
            $orig->server->all(),
            $orig->getContent()
        ) extends \Illuminate\Http\Request {
            public function validated()
            {
                return $this->all();
            }
        };

        $controller->store($mock);
        $this->assertDatabaseHas('actualite', ['titrefr' => 't']);

        $act = Actualite::first();

        // Update via mocked StoreActualiteRequest
        $data2 = $data;
        $data2['titrefr'] = 'updated';

        $orig2 = Request::create('/', 'POST', $data2);
        $mock2 = new class(
            $orig2->query->all(),
            $orig2->request->all(),
            $orig2->attributes->all(),
            $orig2->cookies->all(),
            $orig2->files->all(),
            $orig2->server->all(),
            $orig2->getContent()
        ) extends \Illuminate\Http\Request {
            public function validated()
            {
                return $this->all();
            }
        };

        $controller->update($mock2, $act->idActualite);
        $this->assertEquals('updated', Actualite::find($act->idActualite)->titrefr);
    }

    public function test_storeactualiterequest_prepare_for_validation_and_rules()
    {
        $req = StoreActualiteRequest::create('/','POST', ['dateP' => '10/12/2025']);
        $ref = new \ReflectionMethod(StoreActualiteRequest::class, 'prepareForValidation');
        $ref->setAccessible(true);
        $ref->invoke($req);

        $this->assertStringContainsString('2025-12-10', $req->get('dateP'));

        $rules = (new StoreActualiteRequest())->rules();
        $this->assertArrayHasKey('dateP', $rules);
    }

    public function test_accountcontroller_redirect_if_archived()
    {
        $controller = new AccountController();

        $u = Utilisateur::factory()->create();
        $u->archived_at = now();
        $u->save();

        $ref = new \ReflectionMethod(AccountController::class, 'redirectIfArchived');
        $ref->setAccessible(true);
        $resp = $ref->invoke($controller, $u);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_presencecontroller_extract_class_ids_private()
    {
        $controller = new PresenceController();

        $req1 = Request::create('/','GET',['classe_ids' => '1,2,3']);
        $req2 = Request::create('/','GET',['classe_ids' => [4,5]]);
        $req3 = Request::create('/','GET',['classe_id' => 7]);

        $ref = new \ReflectionMethod(PresenceController::class, 'extractClassIds');
        $ref->setAccessible(true);

        $out1 = $ref->invoke($controller, $req1);
        $out2 = $ref->invoke($controller, $req2);
        $out3 = $ref->invoke($controller, $req3);

        $this->assertEquals([1,2,3], $out1);
        $this->assertEquals([4,5], $out2);
        $this->assertEquals([7], $out3);
    }
}
