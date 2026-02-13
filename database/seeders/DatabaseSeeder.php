<?php
namespace Database\Seeders;

use App\Models\Enfant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        if (! app()->environment('production')) {
            Enfant::factory()->count(10)->create();
            $this->call([
                ActualiteSeeder::class,
                TacheSeeder::class,
            ]);

        }

    }
}
