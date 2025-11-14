<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Utilisateur;

class AccountUpdateSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_put_update_then_roles_sync_via_controller()
    {
        // This mirrors the failing feature test scenario
        // Disable auth and role checks but keep route model binding (SubstituteBindings)
        // Create an admin user and give it the CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $role = Role::factory()->create();
        $account = Utilisateur::factory()->create();

        $putData = [
            'prenom' => 'NewPrenom',
            'nom' => 'NewNom',
            'email' => 'newemail@example.com',
            'languePref' => 'en',
            'statutValidation' => false,
            'roles' => [$role->idRole],
        ];

        $response = $this->put(route('admin.accounts.update', $account->idUtilisateur), $putData);

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['email' => 'newemail@example.com', 'prenom' => 'NewPrenom']);
        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $account->idUtilisateur, 'idRole' => $role->idRole]);
    }
}
