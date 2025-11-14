<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

class LierControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_updateParite_updates_existing_relation()
    {
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();

        // insert pivot
        DB::table('lier')->insert([
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 'parent',
        ]);

        $response = $this->postJson('/presence', []); // dummy to satisfy framework (we'll call controller directly)

        // call controller directly
        $controller = new \App\Http\Controllers\LierController();
        $request = \Illuminate\Http\Request::create('/updateParite', 'POST', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 'tuteur',
        ]);

        $resp = $controller->updateParite($request);
        $this->assertEquals(200, $resp->getStatusCode());

        $this->assertDatabaseHas('lier', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 'tuteur',
        ]);
    }

    public function test_updateParite_returns_404_when_not_found()
    {
        // create valid famille and utilisateur but do NOT insert pivot row
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();

        $controller = new \App\Http\Controllers\LierController();
        $request = \Illuminate\Http\Request::create('/updateParite', 'POST', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 'tuteur',
        ]);

        $resp = $controller->updateParite($request);
        $this->assertEquals(404, $resp->getStatusCode());
    }
}
