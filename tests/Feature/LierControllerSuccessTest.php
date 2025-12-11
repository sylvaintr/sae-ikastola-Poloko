<?php

namespace Tests\Feature;

use App\Http\Controllers\LierController;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LierControllerSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_updateParite_updates_pivot_and_returns_200(): void
    {
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();

        // Attach via pivot table 'lier' using relation if available
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 'pere']);

        $controller = new LierController();

        $request = Request::create('/lier/update', 'POST', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 'mere',
        ]);

        $response = $controller->updateParite($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDatabaseHas('lier', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 'mere',
        ]);
    }
}
