<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Utilisateur;

class UtilisateurControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_400_when_no_name()
    {
        $response = $this->getJson('/api/utilisateurs');
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Veuillez fournir un nom']);
    }

    public function test_search_returns_404_when_no_user_found()
    {
        $response = $this->getJson('/api/utilisateurs?nom=Inexistant');
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Aucun utilisateur trouvé']);
    }

    public function test_search_returns_users_when_match()
    {
        Utilisateur::factory()->create(['nom' => 'Dupont']);

        $response = $this->getJson('/api/utilisateurs?nom=Dup');
        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'Dupont']);
    }
}
