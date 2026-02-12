<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'CA']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-evenement');
        $role->givePermissionTo('access-calendrier');
        $role->givePermissionTo('access-administration');
        $role->givePermissionTo('gerer-presence');
        $role->givePermissionTo('gerer-actualites');
        $role->givePermissionTo('gerer-etiquettes');
        $role->givePermissionTo('gerer-notifications');
        $role->givePermissionTo('gerer-familles');
        $role->givePermissionTo('gerer-utilisateurs');
        $role->givePermissionTo('gerer-roles');
        $role->givePermissionTo('gerer-enfants');
        $role->givePermissionTo('gerer-classes');
        $role->givePermissionTo('gerer-document-obligatoire');
        $role->givePermissionTo('gerer-factures');

        $role = Role::create(['name' => 'parent']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-calendrier');

        $role = Role::create(['name' => 'salarie']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-calendrier');
        $role->givePermissionTo('gerer-presence');

        Role::create(['name' => 'menage']);
        Role::create(['name' => 'BIL']);
        Role::create(['name' => 'Herri Urrats']);
        Role::create(['name' => 'Integrazio Batzordea']);
        Role::create(['name' => 'Vivre en euskara']);
        Role::create(['name' => 'Jardin']);
        Role::create(['name' => 'Bricolage']);
        Role::create(['name' => 'Communication']);
        Role::create(['name' => 'Entretien des batiments']);
        Role::create(['name' => 'Commission Pedagogique']);
        Role::create(['name' => 'Conseil des ikastola']);
        Role::create(['name' => 'ikastolaren besta']);
        Role::create(['name' => 'traduction']);
        Role::create(['name' => 'Subventions']);
        Role::create(['name' => 'Location de l ikastola']);
        Role::create(['name' => 'commission garderie & remplacement des langile absents']);

    }
}
