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

    public function test_recherche_colonne_titre_datatables_actualite_correspond()
    {
        // given
        $unique = 'UniqueTitle' . uniqid();
        $a = Actualite::factory()->create(['titrefr' => $unique]);
        $controller = new ActualiteController();

        // when
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

        // then
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
        $payload = $resp->getData(true);
        $this->assertNotEmpty($payload['data']);
        $this->assertStringContainsString($unique, json_encode($payload['data']));
    }

    public function test_recherche_colonne_etiquettes_datatables_actualite_correspond()
    {
        // given
        $label = 'Label' . uniqid();
        $et = Etiquette::factory()->create(['nom' => $label]);
        $a = Actualite::factory()->create(['titrefr' => 'HasLabel']);
        $a->etiquettes()->attach($et->idEtiquette);
        $controller = new ActualiteController();

        // when
        $req = Request::create('/data', 'GET', [
            'columns' => [
                [
                    'data' => 'etiquettes',
                    'search' => ['value' => $label]
                ]
            ]
        ]);

        $resp = $controller->data($req);

        // then
        $payload = $resp->getData(true);
        $this->assertNotEmpty($payload['data']);
        $this->assertStringContainsString($label, json_encode($payload['data']));
    }

    public function test_datatables_etiquette_recherche_colonne_role_et_filtre_role()
    {
        // given
        $role = Role::factory()->create(['name' => 'RoleSearch' . substr(uniqid(), -6)]);
        $et = Etiquette::factory()->create(['nom' => 'Etag' . uniqid()]);
        // attach role to etiquette
        $et->roles()->attach($role->idRole);
        $controller = new EtiquetteController();

        // when
        // Role filter param
        $reqRole = Request::create('/data', 'GET', ['role' => $role->idRole]);
        $respRole = $controller->data($reqRole);
        $payloadRole = $respRole->getData(true);

        // then
        $this->assertNotEmpty($payloadRole['data']);

        // when (column search)
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

        // then (column search)
        $this->assertNotEmpty($payloadCol['data']);
        $this->assertStringContainsString($role->name, json_encode($payloadCol['data']));
    }
}
