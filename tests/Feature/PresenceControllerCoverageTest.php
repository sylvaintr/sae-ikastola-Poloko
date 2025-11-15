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
        Classe::factory()->create(['nom' => 'A']);
        Classe::factory()->create(['nom' => 'B']);

        $controller = new \App\Http\Controllers\PresenceController();
        $resp = $controller->classes();
        $this->assertEquals(200, $resp->getStatusCode());
        $data = $resp->getData(true);
        $this->assertGreaterThanOrEqual(2, count($data));
        $this->assertArrayHasKey('idClasse', (array)($data[0] ?? []));
    }

    public function test_students_and_status_and_save_flow()
    {
        $classe = Classe::factory()->create();
        $enfant1 = Enfant::factory()->create(['idClasse' => $classe->idClasse, 'idEnfant' => random_int(10000, 99999)]);
        $enfant2 = Enfant::factory()->create(['idClasse' => $classe->idClasse, 'idEnfant' => random_int(10000, 99999)]);

        Role::factory()->create(['name' => 'CA']);
        /** @var \App\Models\Utilisateur&\Illuminate\Contracts\Auth\Authenticatable $admin */
        $admin = \App\Models\Utilisateur::factory()->create();
        $admin->assignRole('CA');

        // students via HTTP route (acting as admin to pass role middleware)
        $studentsResp = $this->actingAs($admin)->getJson(route('presence.students', ['classe_id' => $classe->idClasse]));
        $studentsResp->assertStatus(200);
        $students = $studentsResp->json();
        $this->assertCount(2, $students);

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

        $saveResp = $this->actingAs($admin)->post(route('presence.save'), $payload);
        $saveResp->assertStatus(200);

        // status should return enfant1 present
        $statusResp = $this->actingAs($admin)->getJson(route('presence.status', ['classe_id' => $classe->idClasse, 'date' => $date, 'activite' => 'cantine']));
        $statusResp->assertStatus(200);
        $status = $statusResp->json();
        $this->assertEquals([$enfant1->idEnfant], $status['presentIds']);
    }
}
