<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\EtiquetteController;
use App\Models\Etiquette;
use App\Models\Role;

class EtiquetteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_view_with_etiquettes()
    {
        Etiquette::factory()->count(3)->create();

        $controller = new EtiquetteController();
        $response = $controller->index();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $data = $response->getData();
        $this->assertArrayHasKey('etiquettes', $data);
        $this->assertCount(3, $data['etiquettes']);
    }

    public function test_create_returns_view_with_roles()
    {
        Role::factory()->count(2)->create();

        $controller = new EtiquetteController();
        $response = $controller->create();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $data = $response->getData();
        $this->assertArrayHasKey('roles', $data);
        $this->assertGreaterThanOrEqual(2, count($data['roles']));
    }

    public function test_store_creates_etiquette_and_syncs_roles()
    {
        $roles = Role::factory()->count(2)->create();

        $params = [
            'nom' => 'Tag Test',
            'roles' => [$roles[0]->idRole, $roles[1]->idRole],
        ];

        $request = Request::create('/admin/etiquettes', 'POST', $params);

        $controller = new EtiquetteController();
        $controller->store($request);

        $this->assertTrue(Etiquette::where('nom', 'Tag Test')->exists());
        $et = Etiquette::where('nom', 'Tag Test')->first();
        $this->assertCount(2, $et->roles()->get());
    }

    public function test_edit_returns_view_when_found_and_redirect_when_not_found()
    {
        $et = Etiquette::factory()->create();
        $controller = new EtiquetteController();

        $response = $controller->edit($et->idEtiquette);
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $data = $response->getData();
        $this->assertArrayHasKey('etiquette', $data);

        // Missing id should redirect
        $response2 = $controller->edit(999999);
        $this->assertInstanceOf(RedirectResponse::class, $response2);
    }

    public function test_update_syncs_roles_and_updates_name()
    {
        $roles = Role::factory()->count(3)->create();
        $et = Etiquette::factory()->create(['nom' => 'Old']);

        $params = [
            'nom' => 'NewName',
            'roles' => [$roles[0]->idRole, $roles[1]->idRole],
        ];

        $request = Request::create('/admin/etiquettes/'.$et->idEtiquette, 'PUT', $params);

        $controller = new EtiquetteController();
        $controller->update($request, $et);

        $et->refresh();
        $this->assertEquals('NewName', $et->nom);
        $this->assertCount(2, $et->roles()->get());
    }

    public function test_destroy_deletes_etiquette()
    {
        $et = Etiquette::factory()->create();
        $controller = new EtiquetteController();

        $resp = $controller->destroy($et);

        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertFalse(Etiquette::where('idEtiquette', $et->idEtiquette)->exists());
    }
}
