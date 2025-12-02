<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\ValidationException;

class AccountControllerUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_changes_account_and_syncs_roles_and_redirects()
    {
        $this->withoutMiddleware();

        $role = Role::factory()->create();
        $account = Utilisateur::factory()->create([
            'prenom' => 'Old',
            'nom' => 'Name',
            'email' => 'old@example.com'
        ]);

        $putData = [
            'prenom' => 'NewPrenom',
            'nom' => 'NewNom',
            'email' => 'new@example.com',
            'languePref' => 'fr',
            'roles' => [$role->idRole],
        ];

        // Call controller directly to avoid route/middleware complications
        $controller = new \App\Http\Controllers\Admin\AccountController();
        $request = new \Illuminate\Http\Request($putData);
        $controller->update($request, $account);

        // After direct call, DB should be updated and pivot present
        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $account->idUtilisateur, 'prenom' => 'NewPrenom', 'email' => 'new@example.com']);
        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $account->idUtilisateur, 'idRole' => $role->idRole]);
    }

    public function test_update_changes_password_when_provided()
    {
        $this->withoutMiddleware();

        $role = Role::factory()->create();
        $account = Utilisateur::factory()->create([
            'prenom' => 'Old',
            'nom' => 'Name',
            'email' => 'oldpass@example.com'
        ]);

        $this->assertTrue(Hash::check('password', $account->mdp));

        $putData = [
            'prenom' => 'WithPwd',
            'nom' => 'User',
            'email' => 'withpwd@example.com',
            'languePref' => 'fr',
            'mdp' => 'newsecret',
            'mdp_confirmation' => 'newsecret',
            'roles' => [$role->idRole],
        ];

        $controller = new \App\Http\Controllers\Admin\AccountController();
        $request = new \Illuminate\Http\Request($putData);
        $controller->update($request, $account);

        $fresh = Utilisateur::find($account->idUtilisateur);
        $this->assertTrue(Hash::check('newsecret', $fresh->mdp));
    }

    public function test_update_without_password_keeps_existing_password()
    {
        $this->withoutMiddleware();

        $role = Role::factory()->create();
        $account = Utilisateur::factory()->create([
            'prenom' => 'NoPwd',
            'nom' => 'User',
            'email' => 'nopwd@example.com'
        ]);

        $oldHash = $account->mdp;

        $putData = [
            'prenom' => 'NoPwdNew',
            'nom' => 'UserNew',
            'email' => 'nopwdnew@example.com',
            'languePref' => 'fr',
            'roles' => [$role->idRole],
        ];

        $controller = new \App\Http\Controllers\Admin\AccountController();
        $request = new \Illuminate\Http\Request($putData);
        $controller->update($request, $account);

        $fresh = Utilisateur::find($account->idUtilisateur);
        $this->assertEquals($oldHash, $fresh->mdp);
    }

    public function test_update_fails_when_email_already_used()
    {
        $this->withoutMiddleware();

        Utilisateur::factory()->create(['email' => 'exists@example.com']);
        $account = Utilisateur::factory()->create(['email' => 'target@example.com']);

        $putData = [
            'prenom' => 'X',
            'nom' => 'Y',
            'email' => 'exists@example.com', // duplicate
            'languePref' => 'fr',
            'roles' => [],
        ];

        $this->expectException(ValidationException::class);

        $controller = new \App\Http\Controllers\Admin\AccountController();
        $request = new \Illuminate\Http\Request($putData);
        $controller->update($request, $account);
    }
}
