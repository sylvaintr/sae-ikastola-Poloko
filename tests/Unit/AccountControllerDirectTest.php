<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Utilisateur;
use App\Http\Controllers\Admin\AccountController;

class AccountControllerDirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_controller_update_and_sync()
    {
        $this->withoutMiddleware();

        $role = Role::factory()->create();
        $account = Utilisateur::factory()->create();

        $putData = [
            'prenom' => 'DirPrenom',
            'nom' => 'DirNom',
            'email' => 'direct@example.com',
            'languePref' => 'fr',
            'statutValidation' => false,
            'roles' => [$role->idRole],
        ];

        $controller = new AccountController();

        $request = new Request($putData);

        // Call controller method directly
        $controller->update($request, $account);

        // After direct call, pivot should exist
        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $account->idUtilisateur, 'idRole' => $role->idRole]);
    }
}
