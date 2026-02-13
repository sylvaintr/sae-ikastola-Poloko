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
        Permission::firstOrCreate(['name' => 'access-administration'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access-demande'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access-tache'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access-presence'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access-evenement'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access-calendrier'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-presence'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-actualites'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-etiquettes'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-demandes'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-notifications'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-familles'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-utilisateurs'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-roles'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-enfants'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-classes'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-document-obligatoire'], ['guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gerer-factures'], ['guard_name' => 'web']);
    }
}
