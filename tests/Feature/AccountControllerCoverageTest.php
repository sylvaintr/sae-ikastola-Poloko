<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Role;

class AccountControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_account_and_syncs_roles()
    {
        // Create an admin with CA role to pass middleware
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        // Create a role to assign to the new account
        $role = Role::factory()->create();

        $post = [
            'prenom' => 'Jean',
            'nom' => 'Dupont',
            'email' => 'jean.dupont@example.test',
            'languePref' => 'fr',
            'mdp' => 'password123',
            'mdp_confirmation' => 'password123',
            'roles' => [$role->idRole],
        ];

        $response = $this->actingAs($admin)->post(route('admin.accounts.store'), $post);

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', [
            'email' => 'jean.dupont@example.test',
        ]);

        $created = Utilisateur::where('email', 'jean.dupont@example.test')->first();
        $this->assertNotNull($created);
        $this->assertDatabaseHas('avoir', [
            'idUtilisateur' => $created->idUtilisateur,
            'idRole' => $role->idRole,
        ]);
    }

    public function test_update_modifies_account_and_syncs_roles()
    {
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $existing = Utilisateur::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        // assign initial role
        $existing->rolesCustom()->sync([$role1->idRole => ['model_type' => Utilisateur::class]]);

        $put = [
            'prenom' => 'Paul',
            'nom' => 'Martin',
            'email' => 'paul.martin@example.test',
            'languePref' => 'en',
            'roles' => [$role2->idRole],
        ];

        $response = $this->actingAs($admin)->put(route('admin.accounts.update', $existing->idUtilisateur), $put);

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', [
            'idUtilisateur' => $existing->idUtilisateur,
            'prenom' => 'Paul',
            'nom' => 'Martin',
            'email' => 'paul.martin@example.test',
        ]);

        $this->assertDatabaseHas('avoir', [
            'idUtilisateur' => $existing->idUtilisateur,
            'idRole' => $role2->idRole,
        ]);
    }

    public function test_destroy_removes_account()
    {
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $victim = Utilisateur::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.accounts.destroy', $victim->idUtilisateur));

        $response->assertRedirect(route('admin.accounts.index'));
        $this->assertDatabaseMissing('utilisateur', ['idUtilisateur' => $victim->idUtilisateur]);
    }
}
