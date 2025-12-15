<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\EtiquetteController;
use App\Models\Actualite;
use App\Models\Etiquette;
use App\Models\Role;

class DataTablesFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualite_datatables_column_search_titre_matches()
    {
        $unique = 'UniqueTitle' . uniqid();
        $a = Actualite::factory()->create(['titrefr' => $unique]);

        $controller = new ActualiteController();

        // Simulate DataTables column search on the virtual 'titre' column
        $req = Request::create('/data', 'GET', [
            'columns' => [
                [
                    'data' => 'titre',
                    'search' => ['value' => $unique]
                ]
            ]
        ]);

        $resp = $controller->data($req);
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
        $payload = $resp->getData(true);
        $this->assertNotEmpty($payload['data']);
        $this->assertStringContainsString($unique, json_encode($payload['data']));
    }

    public function test_actualite_datatables_column_search_etiquettes_matches()
    {
        $label = 'Label' . uniqid();
        $et = Etiquette::factory()->create(['nom' => $label]);
        $a = Actualite::factory()->create(['titrefr' => 'HasLabel']);
        $a->etiquettes()->attach($et->idEtiquette);

        $controller = new ActualiteController();

        $req = Request::create('/data', 'GET', [
            'columns' => [
                [
                    'data' => 'etiquettes',
                    'search' => ['value' => $label]
                ]
            ]
        ]);

        $resp = $controller->data($req);
        $payload = $resp->getData(true);
        $this->assertNotEmpty($payload['data']);
        $this->assertStringContainsString($label, json_encode($payload['data']));
    }

    public function test_etiquette_datatables_role_column_search_and_role_filter()
    {
        $role = Role::factory()->create(['name' => 'RoleSearch' . substr(uniqid(), -6)]);
        $et = Etiquette::factory()->create(['nom' => 'Etag' . uniqid()]);
        // attach role to etiquette
        $et->roles()->attach($role->idRole);

        $controller = new EtiquetteController();

        // Role filter param
        $reqRole = Request::create('/data', 'GET', ['role' => $role->idRole]);
        $respRole = $controller->data($reqRole);
        $payloadRole = $respRole->getData(true);
        $this->assertNotEmpty($payloadRole['data']);

        // Column search on 'roles'
        $reqCol = Request::create('/data', 'GET', [
            'columns' => [
                [
                    'data' => 'roles',
                    'search' => ['value' => $role->name]
                ]
            ]
        ]);
        $respCol = $controller->data($reqCol);
        $payloadCol = $respCol->getData(true);
        $this->assertNotEmpty($payloadCol['data']);
        $this->assertStringContainsString($role->name, json_encode($payloadCol['data']));
    }
}
