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
        $role = Role::firstOrCreate(['name' => 'CA']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-evenement');
        $role->givePermissionTo('access-calendrier');
        $role->givePermissionTo('access-administration');
        $role->givePermissionTo('gerer-presence');
        $role->givePermissionTo('gerer-actualites');
        $role->givePermissionTo('gerer-etiquettes');

        $role = Role::firstOrCreate(['name' => 'parent']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-calendrier');

        $role = Role::firstOrCreate(['name' => 'salarie']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-calendrier');
        $role->givePermissionTo('gerer-presence');
    }
}
