<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'access-administration']);
        Permission::create(['name' => 'access-demende']);
        Permission::create(['name' => 'access-tache']);
        Permission::create(['name' => 'access-presence']);
        Permission::create(['name' => 'access-evenement']);
        Permission::create(['name' => 'access-calendrier']);

    }
}
