<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Utilisateur;

class AccountUpdateSyncTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Utilisateur */
    protected $adminUser;
    /**
     * Set up an admin user and authenticate it.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);
    }

    public function test_put_update_then_roles_sync_via_controller()
    {
        // given
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

        // when
        $response = $this->put(route('admin.accounts.update', $account->idUtilisateur), $putData);

        // then
        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['email' => 'newemail@example.com', 'prenom' => 'NewPrenom']);
        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $account->idUtilisateur, 'idRole' => $role->idRole]);
    }
}
