<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Role;

class PresenceControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_classes_returns_list()
    {
        // given
        Classe::factory()->create(['nom' => 'A']);
        Classe::factory()->create(['nom' => 'B']);

        // when
        $controller = new \App\Http\Controllers\PresenceController();
        $resp = $controller->classes();

        // then
        $this->assertEquals(200, $resp->getStatusCode());
        $data = $resp->getData(true);
        $this->assertGreaterThanOrEqual(2, count($data));
        $this->assertArrayHasKey('idClasse', (array)($data[0] ?? []));
    }

    public function test_students_and_status_and_save_flow()
    {
        // given
        $classe = Classe::factory()->create();
        $enfant1 = Enfant::factory()->create(['idClasse' => $classe->idClasse, 'idEnfant' => random_int(10000, 99999)]);
        $enfant2 = Enfant::factory()->create(['idClasse' => $classe->idClasse, 'idEnfant' => random_int(10000, 99999)]);

        \App\Models\Role::firstOrCreate(['name' => 'CA']);
        /** @var \App\Models\Utilisateur&\Illuminate\Contracts\Auth\Authenticatable $admin */
        $admin = \App\Models\Utilisateur::factory()->create();
        $admin->assignRole('CA');

        // when
        // students via HTTP route (acting as admin to pass role middleware)
        $studentsResp = $this->actingAs($admin)->getJson(route('presence.students', ['classe_ids' => [$classe->idClasse]]));
        $studentsResp->assertStatus(200);
        $students = $studentsResp->json();

        // then
        $this->assertCount(2, $students);

        // given (save presence)
        // save presence: mark enfant1 present, enfant2 absent
        $date = now()->toDateString();
        $payload = [
            'date' => $date,
            'activite' => 'cantine',
            'items' => [
                ['idEnfant' => $enfant1->idEnfant, 'present' => true],
                ['idEnfant' => $enfant2->idEnfant, 'present' => false],
            ],
        ];

        // when (save)
        $saveResp = $this->actingAs($admin)->post(route('presence.save'), $payload);

        // then (save)
        $saveResp->assertStatus(200);

        // when (status)
        $statusResp = $this->actingAs($admin)->getJson(route('presence.status', ['classe_ids' => [$classe->idClasse], 'date' => $date, 'activite' => 'cantine']));
        $statusResp->assertStatus(200);
        $status = $statusResp->json();

        // then (status)
        $this->assertEquals([$enfant1->idEnfant], $status['presentIds']);
    }

    public function test_students_endpoint_handles_multiple_classes()
    {
        // given
        $classeA = Classe::factory()->create(['nom' => 'Classe A']);
        $classeB = Classe::factory()->create(['nom' => 'Classe B']);
        Enfant::factory()->create(['idClasse' => $classeA->idClasse]);
        Enfant::factory()->create(['idClasse' => $classeB->idClasse]);

        \App\Models\Role::firstOrCreate(['name' => 'CA']);
        /** @var \App\Models\Utilisateur&\Illuminate\Contracts\Auth\Authenticatable $admin */
        $admin = \App\Models\Utilisateur::factory()->create();
        $admin->assignRole('CA');

        // when
        $response = $this->actingAs($admin)->getJson(route('presence.students', [
            'classe_ids' => [$classeA->idClasse, $classeB->idClasse],
        ]));

        // then
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(2, $data);
        $this->assertContains('Classe A', array_column($data, 'classe_nom'));
        $this->assertContains('Classe B', array_column($data, 'classe_nom'));
    }
}
