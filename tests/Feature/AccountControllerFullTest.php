<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountController;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AccountControllerFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_account_and_redirects(): void
    {
        $role = Role::factory()->create();

        $controller = new AccountController();

        $request = Request::create('/admin/accounts/store', 'POST', [
            'prenom' => 'Prenom',
            'nom' => 'Nom',
            'email' => 'user+' . uniqid() . '@example.test',
            'languePref' => 'fr',
            'mdp' => 'password123',
            'mdp_confirmation' => 'password123',
            'roles' => [$role->idRole],
        ]);

        $response = $controller->store($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('utilisateur', ['prenom' => 'Prenom', 'nom' => 'Nom']);
    }

    public function test_update_and_destroy(): void
    {
        $role = Role::factory()->create();
        $user = Utilisateur::factory()->create();
        $controller = new AccountController();

        $request = Request::create('/admin/accounts/update', 'PUT', [
            'prenom' => 'NewPrenom',
            'nom' => 'NewNom',
            'email' => 'updated+' . uniqid() . '@example.test',
            'languePref' => 'en',
            'roles' => [$role->idRole],
        ]);

        $response = $controller->update($request, $user);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $user->idUtilisateur, 'prenom' => 'NewPrenom']);

        $respDel = $controller->destroy($user);
        $this->assertEquals(302, $respDel->getStatusCode());
        $this->assertDatabaseMissing('utilisateur', ['idUtilisateur' => $user->idUtilisateur]);
    }
}
