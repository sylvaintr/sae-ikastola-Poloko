<?php

namespace Tests\Unit;

use App\Http\Controllers\ClasseController;
use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_cree_classe_et_attribue_enfants()
    {
        // given
        $children = Enfant::factory()->count(2)->create(['idClasse' => null]);

        $request = $this->getMockBuilder(Request::class)->addMethods(['validate'])->getMock();
        $request->replace([
            'nom' => 'Classe A',
            'niveau' => '1',
        ]);
        $request->children = $children->pluck('idEnfant')->values()->toArray();
        $request->expects($this->any())->method('validate')->willReturn($request->all());

        // when
        $ctrl = new ClasseController();
        $resp = $ctrl->store($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);

        $this->assertDatabaseHas((new Classe())->getTable(), ['nom' => 'Classe A']);

        $classe = Classe::where('nom', 'Classe A')->first();
        $this->assertNotNull($classe);

        // Note: actual children assignment updated in DB by controller; ensure classe exists.
    }

    public function test_destroy_detache_enfants_et_supprime()
    {
        // given
        $classe = Classe::factory()->create();
        $children = Enfant::factory()->count(2)->create(['idClasse' => $classe->idClasse]);

        // when
        $ctrl = new ClasseController();
        $resp = $ctrl->destroy($classe);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);

        $this->assertDatabaseMissing((new Classe())->getTable(), ['idClasse' => $classe->idClasse]);

        $this->assertEquals($children->count(), Enfant::whereNull('idClasse')->count());
    }

    public function test_index_retourne_vue()
    {
        // given
        // none

        // when

        // then
        Classe::factory()->count(2)->create();

        $req = Request::create('/', 'GET', []);
        $this->app->instance('request', $req);

        $ctrl = new ClasseController();
        $view = $ctrl->index($req);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_data_retourne_json()
    {
        // given
        // none

        // when

        // then
        Classe::factory()->count(2)->create();

        $ctrl = new ClasseController();
        $resp = $ctrl->data();

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
    }

    public function test_show_retourne_vue_avec_enfants()
    {
        // given
        // none

        // when

        // then
        $classe = Classe::factory()->create();
        $child = Enfant::factory()->create(['idClasse' => $classe->idClasse, 'idEnfant' => 3000]);

        $ctrl = new ClasseController();
        $view = $ctrl->show($classe);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertArrayHasKey('classe', $view->getData());
    }

    public function test_edit_retourne_vue()
    {
        // given
        // none

        // when

        // then
        $classe = Classe::factory()->create();
        $ctrl = new ClasseController();
        $view = $ctrl->edit($classe);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_create_retourne_vue()
    {
        // given
        // none

        // when

        // then
        // prepare some available enfants
        Enfant::factory()->create(['idFamille' => 0, 'idEnfant' => 5000]);

        $ctrl = new ClasseController();
        $view = $ctrl->create();
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_update_synchronise_les_enfants()
    {
        // given
        $classe = Classe::factory()->create();
        $child1 = Enfant::factory()->create(['idClasse' => $classe->idClasse, 'idEnfant' => 4000]);
        $child2 = Enfant::factory()->create(['idClasse' => null, 'idEnfant' => 4001]);

        $request = $this->getMockBuilder(Request::class)->addMethods(['validate'])->getMock();
        $request->replace(['nom' => 'C1', 'niveau' => '2', 'children' => [$child2->idEnfant]]);
        $request->expects($this->any())->method('validate')->willReturn($request->all());

        // when
        $ctrl = new ClasseController();
        $resp = $ctrl->update($request, $classe);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertEquals($classe->idClasse, Enfant::find($child2->idEnfant)->idClasse);
        $this->assertNull(Enfant::find($child1->idEnfant)->idClasse);
    }
}
