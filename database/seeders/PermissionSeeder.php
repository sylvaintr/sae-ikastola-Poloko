<?php
namespace Database\Seeders;

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
        Permission::create(['name' => 'access-demande']);
        Permission::create(['name' => 'access-tache']);
        Permission::create(['name' => 'access-presence']);
        Permission::create(['name' => 'access-evenement']);
        Permission::create(['name' => 'access-calendrier']);
        Permission::create(['name' => 'gerer-presence']);
        Permission::create(['name' => 'gerer-actualites']);
        Permission::create(['name' => 'gerer-etiquettes']);
        Permission::create(['name' => 'gerer-notifications']);
        Permission::create(['name' => 'gerer-familles']);
        Permission::create(['name' => 'gerer-utilisateurs']);
        Permission::create(['name' => 'gerer-roles']);
        Permission::create(['name' => 'gerer-enfants']);
        Permission::create(['name' => 'gerer-classes']);
        Permission::create(['name' => 'gerer-document-obligatoire']);
        Permission::create(['name' => 'gerer-factures']);
    }
}
