<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

class LierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_parite_success()
    {
        $famille = Famille::factory()->create();
        $user1 = Utilisateur::factory()->create();
        $user2 = Utilisateur::factory()->create();

        // Insertion manuelle
        DB::table('lier')->insert([
            ['idFamille' => $famille->idFamille, 'idUtilisateur' => $user1->idUtilisateur, 'parite' => 50],
            ['idFamille' => $famille->idFamille, 'idUtilisateur' => $user2->idUtilisateur, 'parite' => 50],
        ]);

        $payload = [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user1->idUtilisateur,
            'parite' => 60,
        ];

        $response = $this->putJson('/api/lier/update-parite', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('lier', ['idFamille' => $famille->idFamille, 'idUtilisateur' => $user1->idUtilisateur, 'parite' => 60]);
    }

    public function test_update_parite_validation_error()
    {
        $response = $this->putJson('/api/lier/update-parite', ['parite' => 'string']);
        $response->assertStatus(422);
    }

    public function test_update_parite_not_found()
    {
        // 1. Créer Famille et User pour passer la validation des IDs
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();

        // 2. SOLUTION RADICALE : On vide entièrement la table pivot.
        // Il est physiquement impossible qu'un lien subsiste après ça.
        DB::table('lier')->delete();

        // 3. Appel API
        $payload = [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
            'parite' => 50,
        ];

        $response = $this->putJson('/api/lier/update-parite', $payload);

        // 4. Le contrôleur ne peut QUE retourner 404
        $response->assertStatus(404);
    }
}

