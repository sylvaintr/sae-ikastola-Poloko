<?php
namespace Tests\Feature;

use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        // Création de l'admin CA pour l'accès aux routes
        \Spatie\Permission\Models\Role::create(['name' => 'CA']);
        $this->admin = Utilisateur::factory()->create();
        $this->admin->assignRole('CA');

        // Rôle de test
        $this->role = Role::create(['name' => 'editor']);
    }

    public function test_index_shows_roles()
    {
        Role::create(['name' => 'role1']);
        Role::create(['name' => 'role2']);

        $user = Utilisateur::factory()->create();
        $user->assignRole('CA'); // ensure has admin

        $this->actingAs($user)
            ->get(route('admin.roles.index'))
            ->assertStatus(200)
            ->assertSee('role1')
            ->assertSee('role2');
    }

    public function test_show_displays_role_permissions()
    {
        $role = Role::create(['name' => 'tester']);
        $p1   = Permission::create(['name' => 'perm.a']);
        $role->givePermissionTo($p1);

        $user = Utilisateur::factory()->create();
        $user->assignRole('CA');

        $this->actingAs($user)
            ->get(route('admin.roles.show', $role))
            ->assertStatus(200)
            ->assertSee('perm.a');
    }

    public function test_attach_multiple_permissions()
    {
        $role = Role::create(['name' => 'tester2']);
        $p1   = Permission::create(['name' => 'perm.x']);
        $p2   = Permission::create(['name' => 'perm.y']);

        $user = Utilisateur::factory()->create();
        $user->assignRole('CA');

        $this->actingAs($user)
            ->post(route('admin.roles.permissions.attach', $role), [
                'permissions' => [$p1->id, $p2->id],
            ])
            ->assertRedirect();

        $this->assertTrue($role->hasPermissionTo('perm.x'));
        $this->assertTrue($role->hasPermissionTo('perm.y'));
    }

    public function test_detach_permission()
    {
        $role = Role::create(['name' => 'tester3']);
        $p    = Permission::create(['name' => 'perm.z']);
        $role->givePermissionTo($p);

        $user = Utilisateur::factory()->create();
        $user->assignRole('CA');

        $this->actingAs($user)
            ->delete(route('admin.roles.permissions.detach', [$role, $p->id]))
            ->assertRedirect();

        $this->assertFalse($role->hasPermissionTo('perm.z'));
    }

    public function test_respondNotFound_returns_session_error()
    {
        $role = Role::create(['name' => 'role_no_input']);

        $user = Utilisateur::factory()->create();
        $user->assignRole('CA');

        $this->actingAs($user)
            ->post(route('admin.roles.permissions.attach', $role), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['permission' => 'Permission introuvable.']);
    }

    /**
     * Cas : Attacher une permission avec un ID qui n'existe pas.
     */
    public function test_attach_with_non_existent_permission_id()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.roles.permissions.attach', $this->role), [
                'permissions' => [999], // ID inexistant
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['permission' => 'Permission introuvable.']);
    }

    /**
     * Cas : Attacher via un nom de permission au lieu d'un ID.
     * Votre contrôleur supporte is_numeric($input) ? Permission::find : Permission::where('name').
     */
    public function test_attach_using_permission_name_string()
    {
        Permission::create(['name' => 'publish-articles']);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.permissions.attach', $this->role), [
                'permissions' => ['publish-articles'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($this->role->hasPermissionTo('publish-articles'));
    }

    /**
     * Cas : Mélange de permissions valides et invalides.
     * Vérifie que les valides sont attachées et que le message d'erreur mentionne l'introuvable.
     */
    public function test_attach_mixed_valid_and_invalid_permissions()
    {
        $p1 = Permission::create(['name' => 'valid-perm']);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.permissions.attach', $this->role), [
                'permissions' => [$p1->id, 'invalid-perm-name'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', function ($msg) {
                return str_contains($msg, 'Certaines permissions introuvables: invalid-perm-name');
            });

        $this->assertTrue($this->role->hasPermissionTo('valid-perm'));
    }

    /**
     * Cas : Utilisation du champ 'permission' (singulier) au lieu de 'permissions' (tableau).
     * Votre code fait : $request->input('permissions', $request->input('permission'))
     */
    public function test_attach_using_single_permission_input_field()
    {
        $p1 = Permission::create(['name' => 'single-perm']);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.permissions.attach', $this->role), [
                'permission' => $p1->id, // Champ au singulier
            ])
            ->assertRedirect();

        $this->assertTrue($this->role->hasPermissionTo('single-perm'));
    }

    /**
     * Cas : Détacher une permission inexistante.
     */
    public function test_detach_non_existent_permission()
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.roles.permissions.detach', [$this->role, 999]))
            ->assertRedirect()
            ->assertSessionHasErrors(['permission' => 'Permission introuvable.']);
    }

    /**
     * Cas : Détacher par le NOM au lieu de l'ID.
     */
    public function test_detach_using_name_instead_of_id()
    {
        $p = Permission::create(['name' => 'delete-posts']);
        $this->role->givePermissionTo($p);

        $this->actingAs($this->admin)
            ->delete(route('admin.roles.permissions.detach', [$this->role, 'delete-posts']))
            ->assertRedirect()
            ->assertSessionHas('success', 'Permission supprimée du rôle.');

        $this->assertFalse($this->role->hasPermissionTo('delete-posts'));
    }

    /**
     * Cas : Attacher une permission déjà possédée (Idempotence).
     * Spatie gère cela, mais on teste que votre contrôleur ne plante pas.
     */
    public function test_attach_already_assigned_permission()
    {
        $p = Permission::create(['name' => 'already-have']);
        $this->role->givePermissionTo($p);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.permissions.attach', $this->role), [
                'permissions' => [$p->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Doit toujours l'avoir une seule fois
        $this->assertTrue($this->role->hasPermissionTo('already-have'));
    }
}
