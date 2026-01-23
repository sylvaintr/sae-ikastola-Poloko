<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Utilisateur;

class PivotSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_custom_sync_definit_id_utilisateur_sur_pivot()
    {
        // given
        // none

        // when

        // then
        $this->withoutMiddleware();

        // Create a role and a user with an explicit id to avoid auto-increment edge cases
        $role = Role::factory()->create();
        $user = Utilisateur::factory()->create();

        // Ensure the model is fresh from DB
        $user = Utilisateur::find($user->idUtilisateur);

        // Perform the sync which previously failed in controller tests
        $rolesToSync = [];
        $rolesToSync[$role->idRole] = ['model_type' => Utilisateur::class];

        $user->rolesCustom()->sync($rolesToSync);

        // Assert pivot row exists and has idUtilisateur set
        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $user->idUtilisateur, 'idRole' => $role->idRole]);
    }

    public function test_mise_a_jour_puis_sync_conserve_la_cle_primaire()
    {
        // given
        // none

        // when

        // then
        $this->withoutMiddleware();

        $role = Role::factory()->create();
        $user = Utilisateur::factory()->create();

        // Simulate controller update: perform an update then sync roles
        $user->update(['prenom' => 'UpdatedPrenom']);

        $user = Utilisateur::find($user->idUtilisateur);

        $rolesToSync = [];
        $rolesToSync[$role->idRole] = ['model_type' => Utilisateur::class];

        $user->rolesCustom()->sync($rolesToSync);

        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $user->idUtilisateur, 'idRole' => $role->idRole]);
    }
}
