<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;
use App\Models\Utilisateur;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_accounts()
    {
        Utilisateur::factory()->count(3)->create();

        Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $response = $this->actingAs($admin)->get(route('admin.accounts.index'));
        $response->assertStatus(200);
        $response->assertViewHas('accounts');
    }

    public function test_create_returns_view_with_roles()
    {
        Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $response = $this->actingAs($admin)->get(route('admin.accounts.create'));
        $response->assertStatus(200);
        $response->assertViewHas('roles');
    }

    public function test_store_creates_account_and_syncs_roles()
    {
        $role = Role::factory()->create();
        Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $email = 'jean+' . uniqid() . '@example.test';
        
        $payload = [
            'prenom' => 'Jean',
            'nom' => 'Valjean',
            'email' => $email,
            'languePref' => 'fr',
            'mdp' => 'password123',
            'mdp_confirmation' => 'password123',
            'roles' => [$role->idRole],
        ];

        $response = $this->actingAs($admin)->post(route('admin.accounts.store'), $payload);

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['email' => $email]);

        $created = Utilisateur::where('email', $email)->first();
        $this->assertNotNull($created);
        $this->assertDatabaseHas('avoir', ['idUtilisateur' => $created->idUtilisateur, 'idRole' => $role->idRole]);
    }

    public function test_show_edit_update_validate_and_destroy()
    {
        $role = Role::factory()->create();
        Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $account = Utilisateur::factory()->create();
        $account->rolesCustom()->attach([$role->idRole => ['model_type' => Utilisateur::class]]);

        $response = $this->actingAs($admin)->get(route('admin.accounts.show', $account));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('admin.accounts.edit', $account));
        $response->assertStatus(200);

        $updatePayload = [
            'prenom' => 'Updated',
            'nom' => 'Name',
            'email' => 'updated@example.test',
            'languePref' => 'en',
            'roles' => [$role->idRole],
            'statutValidation' => 1,
        ];

        $response = $this->actingAs($admin)->put(route('admin.accounts.update', $account), $updatePayload);
        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['email' => 'updated@example.test']);

        // validate account
        $response = $this->actingAs($admin)->patch(route('admin.accounts.validate', $account));
        $response->assertRedirect(route('admin.accounts.index'));
        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $account->idUtilisateur, 'statutValidation' => 1]);

        // destroy
        $response = $this->actingAs($admin)->delete(route('admin.accounts.destroy', $account));
        $response->assertRedirect(route('admin.accounts.index'));
        $this->assertDatabaseMissing('utilisateur', ['idUtilisateur' => $account->idUtilisateur]);
    }

    public function test_archive_action_disables_other_operations()
    {
        $role = Role::factory()->create();
        Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $account = Utilisateur::factory()->create();
        $account->rolesCustom()->attach([$role->idRole => ['model_type' => Utilisateur::class]]);

        $archiveResponse = $this->actingAs($admin)->patch(route('admin.accounts.archive', $account));
        $archiveResponse->assertRedirect(route('admin.accounts.show', $account));
        $this->assertNotNull($account->fresh()->archived_at);

        $this->actingAs($admin)
            ->get(route('admin.accounts.edit', $account))
            ->assertRedirect(route('admin.accounts.show', $account));

        $this->actingAs($admin)
            ->put(route('admin.accounts.update', $account), [
                'prenom' => 'Attempt',
                'nom' => 'Change',
                'email' => 'attempt@example.test',
                'languePref' => 'fr',
                'roles' => [$role->idRole],
            ])
            ->assertRedirect(route('admin.accounts.show', $account));

        $this->actingAs($admin)
            ->patch(route('admin.accounts.validate', $account))
            ->assertRedirect(route('admin.accounts.show', $account));

        $this->actingAs($admin)
            ->delete(route('admin.accounts.destroy', $account))
            ->assertRedirect(route('admin.accounts.show', $account));

        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $account->idUtilisateur]);
    }
}