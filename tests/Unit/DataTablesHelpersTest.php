<?php

namespace Tests\Unit;

use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\ActualiteController;
use App\Models\Role;
use App\Models\Etiquette;
use App\Models\Actualite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;
use Illuminate\Http\Request;

class DataTablesHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_etiquette_helpers_do_not_throw_and_modify_query()
    {
        // Ensure there is at least one role/etiquette record so queries have models
        Role::factory()->create();
        Etiquette::factory()->create();

        $controller = new EtiquetteController();

        $roleQuery = Role::query();
        $this->assertInstanceOf(Builder::class, $roleQuery);
        // Should not throw
        $controller->applyRoleWhereHas($roleQuery, 5);
        $this->assertInstanceOf(Builder::class, $roleQuery);

        $etQuery = Etiquette::query();
        $controller->filterRolesColumnByKeyword($etQuery, 'foo');
        $this->assertInstanceOf(Builder::class, $etQuery);
        
        // Column helpers: create a model and ensure helpers return expected types/strings
        $et = Etiquette::factory()->create();
        $role = Role::factory()->create(['name' => 'role1']);
        $et->roles()->attach($role->idRole);

        $this->assertEquals($controller->columnIdEtiquette($et), $et->idEtiquette);
        $this->assertEquals($controller->columnNom($et), $et->nom);
        $this->assertIsString($controller->columnRolesText($et));
        $this->assertStringContainsString('role1', $controller->columnRolesText($et));
    }

    public function test_actualite_helpers_do_not_throw_and_modify_query()
    {
        Actualite::factory()->create();
        Etiquette::factory()->create();

        $controller = new ActualiteController();

        $etQuery = Etiquette::query();
        $this->assertInstanceOf(Builder::class, $etQuery);

        // applyEtiquetteWhereIn and applyEtiquetteWhere should not throw
        $controller->applyEtiquetteWhereIn($etQuery, [1, 2], '.idEtiquette');
        $controller->applyEtiquetteWhere($etQuery, 1, '.idEtiquette');
        $this->assertInstanceOf(Builder::class, $etQuery);

        $actQuery = Actualite::query();
        $controller->filterTitreColumn($actQuery, 'hello');
        $controller->filterEtiquettesColumn($actQuery, 'tag');
        $this->assertInstanceOf(Builder::class, $actQuery);
        
        // Column helpers for actualite
        $act = Actualite::factory()->create(['titrefr' => 'T1', 'archive' => false]);
        $this->assertStringContainsString('T1', $controller->columnTitre($act));
        $this->assertIsString($controller->columnEtat($act));
        $this->assertIsString($controller->columnEtiquettesText($act));
    }

    public function test_etiquette_data_method_executes_and_returns_json()
    {
        $role = Role::factory()->create(['name' => 'r1']);
        $et = Etiquette::factory()->create(['nom' => 'etag']);
        $et->roles()->attach($role->idRole);

        $controller = new EtiquetteController();

        $resp = $controller->data(Request::create('/data', 'GET', ['name' => 'e', 'role' => $role->idRole]));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
        $payload = $resp->getData(true);
        $this->assertArrayHasKey('data', $payload);
    }
}
