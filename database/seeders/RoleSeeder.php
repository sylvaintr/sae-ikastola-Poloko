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
        $role = Role::firstOrCreate(['name' => 'CA']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('gerer-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-evenement');
        $role->givePermissionTo('gerer-evenement');
        $role->givePermissionTo('access-calendrier');
        $role->givePermissionTo('access-administration');
        $role->givePermissionTo('gerer-presence');
        $role->givePermissionTo('gerer-actualites');
        $role->givePermissionTo('gerer-etiquettes');
        $role->givePermissionTo('gerer-tache');
        $role->givePermissionTo('gerer-notifications');
        $role->givePermissionTo('gerer-familles');
        $role->givePermissionTo('gerer-utilisateurs');
        $role->givePermissionTo('gerer-roles');
        $role->givePermissionTo('gerer-enfants');
        $role->givePermissionTo('gerer-classes');
        $role->givePermissionTo('gerer-document-obligatoire');
        $role->givePermissionTo('gerer-factures');

        $role = Role::firstOrCreate(['name' => 'parent']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-calendrier');

        $role = Role::firstOrCreate(['name' => 'salarie']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-calendrier');
        $role->givePermissionTo('gerer-presence');

        Role::firstOrCreate(['name' => 'menage']);
        Role::firstOrCreate(['name' => 'BIL']);
        Role::firstOrCreate(['name' => 'Herri Urrats']);
        Role::firstOrCreate(['name' => 'Integrazio Batzordea']);
        Role::firstOrCreate(['name' => 'Vivre en euskara']);
        Role::firstOrCreate(['name' => 'Jardin']);
        Role::firstOrCreate(['name' => 'Bricolage']);
        Role::firstOrCreate(['name' => 'Communication']);
        Role::firstOrCreate(['name' => 'Entretien des batiments']);
        Role::firstOrCreate(['name' => 'Commission Pedagogique']);
        Role::firstOrCreate(['name' => 'Conseil des ikastola']);
        Role::firstOrCreate(['name' => 'ikastolaren besta']);
        Role::firstOrCreate(['name' => 'traduction']);
        Role::firstOrCreate(['name' => 'Subventions']);
        Role::firstOrCreate(['name' => 'Location de l ikastola']);
        Role::firstOrCreate(['name' => 'commission garderie & remplacement des langile absents']);
    }
}
