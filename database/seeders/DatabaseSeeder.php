<?php
namespace Database\Seeders;

use App\Models\Facture;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Utilisateur::factory()->create([

            'idUtilisateur' => 1,

        ]);

        Famille::factory()->create([

            'idFamille' => 1,

        ])->utilisateurs()->attach(1);

        Facture::factory()->count(10)->create(['previsionnel' => true, 'dateC' => now()->subMonth(), 'idUtilisateur' => 1, 'idFamille' => 1]);

        Facture::factory()->create(['previsionnel' => false, 'idUtilisateur' => 1, 'idFamille' => 1, 'dateC' => now()]);

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            ActualiteSeeder::class,
        ]);
    }
}
