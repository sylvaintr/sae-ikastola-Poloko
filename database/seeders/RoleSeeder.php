<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

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
        $role->givePermissionTo('gerer-demandes');

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
