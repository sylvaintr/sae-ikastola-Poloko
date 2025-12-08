<?php

namespace Database\Seeders;

use App\Models\Actualite;
use Illuminate\Database\Seeder;

class ActualiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Actualite::factory()->count(30)->create();
    }
}
