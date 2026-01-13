<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Actualite;
use App\Models\Etiquette;
use App\Models\Role;
use App\Models\Utilisateur;
use App\Http\Controllers\ActualiteController;

class ActualiteControllerIndexValidSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_applies_selected_etiquettes_when_user_has_rights()
    {
        $this->withoutMiddleware();

        // Avoid schema migration in test path
        Schema::shouldReceive('hasColumn')->andReturn(true);

        // Prepare data
        $et1 = Etiquette::factory()->create();
        $et2 = Etiquette::factory()->create();

        $role = Role::create(['name' => 'ROLE_T', 'guard_name' => 'web']);
        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach([$role->idRole => ['model_type' => Utilisateur::class]]);

        // give role access to et1 via posseder pivot
        DB::table('posseder')->insert(['idRole' => $role->idRole, 'idEtiquette' => $et1->idEtiquette]);

        // actualites: one with et1, one with et2
        $a = Actualite::factory()->create(['type' => 'private', 'archive' => false, 'dateP' => now()]);
        $b = Actualite::factory()->create(['type' => 'private', 'archive' => false, 'dateP' => now()]);

        $a->etiquettes()->attach($et1->idEtiquette);
        $b->etiquettes()->attach($et2->idEtiquette);

        // authenticate
        $this->be($user);

        // selected etiquettes includes et1
        $request = Request::create('/', 'GET', ['etiquettes' => [$et1->idEtiquette]]);

        $controller = new ActualiteController();
        $resp = $controller->index($request);

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $actualites = $resp->getData()['actualites'];

        $ids = collect($actualites->items())->pluck('idActualite')->all();
        $this->assertContains($a->idActualite, $ids);
        $this->assertNotContains($b->idActualite, $ids);
    }
}
