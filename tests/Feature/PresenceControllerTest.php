<?php
namespace Tests\Feature;

use App\Http\Controllers\PresenceController;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class PresenceControllerTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Création d'un utilisateur admin pour l'authentification
        // Adaptez selon votre système d'authentification (ex: Spatie Roles)
        $this->admin = Utilisateur::factory()->create();
        // Ensure CA role exists and assign to admin so admin routes are accessible in tests
        $role = Role::firstOrCreate(['name' => 'CA'], ['guard_name' => 'web']);
        $this->admin->roles()->attach($role->idRole);
    }

    public function test_classe_a_les_methodes_attendues(): void
    {
        // given
        // no setup required

        // when
        $hasClasses  = method_exists(PresenceController::class, 'classes');
        $hasStudents = method_exists(PresenceController::class, 'students');
        $hasStatus   = method_exists(PresenceController::class, 'status');
        $hasSave     = method_exists(PresenceController::class, 'save');

        // then
        $this->assertTrue($hasClasses);
        $this->assertTrue($hasStudents);
        $this->assertTrue($hasStatus);
        $this->assertTrue($hasSave);
    }

    public function test_eleves_sans_classe_retourne_tableau_vide(): void
    {
        // given
        $controller = new PresenceController();
        $request    = Request::create('/students', 'GET');

        // when
        $response = $controller->students($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    public function test_status_sans_param_retourne_presentIds_vide(): void
    {
        // given
        $controller = new PresenceController();
        $request    = Request::create('/status', 'GET');

        // when
        $response = $controller->status($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertArrayHasKey('presentIds', $data);
        $this->assertEquals([], $data['presentIds']);
    }

    public function test_enregistrement_champs_manquants_lance_exception_validation(): void
    {
        $this->expectException(ValidationException::class);
        // given
        $controller = new PresenceController();
        $request    = Request::create('/save', 'POST', []);

        // when / then
        $controller->save($request);
    }

    /** @test */
    public function test_students_extracts_ids_when_legacy_classe_id_parameter_is_an_array()
    {
        // given
        $class1 = Classe::factory()->create();
        $class2 = Classe::factory()->create();

        $student1 = Enfant::factory()->create(['idClasse' => $class1->idClasse]);
        $student2 = Enfant::factory()->create(['idClasse' => $class2->idClasse]);

        // when
        $response = $this->actingAs($this->admin)
            ->getJson(route('presence.students', [
                'classe_id' => [$class1->idClasse, $class2->idClasse],
            ]));

        // then
        $response->assertStatus(200);

        $response->assertJsonFragment(['idEnfant' => $student1->idEnfant]);
        $response->assertJsonFragment(['idEnfant' => $student2->idEnfant]);
        $response->assertJsonCount(2);
    }
}
