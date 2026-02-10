<?php

namespace Database\Seeders;

use App\Models\Facture;
use App\Models\Famille;
use Illuminate\Database\Seeder;
use App\Models\Tache;
use App\Models\Utilisateur;
use App\Models\Enfant;
use Database\Factories\LierFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Enfant::factory()->count(10)->create();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            TacheSeeder::class,
            UserSeeder::class,
            ActualiteSeeder::class,
        ]);
    }
}
