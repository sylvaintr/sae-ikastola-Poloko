<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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

        $role = Role::create(['name' => 'parent']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-tache');
        $role->givePermissionTo('access-calendrier');

        $role = Role::create(['name' => 'salarie']);
        $role->givePermissionTo('access-demande');
        $role->givePermissionTo('access-presence');
        $role->givePermissionTo('access-calendrier');

    }
}
