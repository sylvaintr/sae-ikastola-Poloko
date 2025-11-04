<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user with role CA
        $userCA = User::create([
            'name' => 'CA',
            'email' => 'ca@example.com',
            'password' => bcrypt('CA'),
        ]);

        $userCA->assignRole('CA');

        $userP = User::create([
            'name' => 'parent',
            'email' => 'parent@example.com',
            'password' => bcrypt('parent'),
        ]);

        $userP->assignRole('parent');

        // Create a user with role salarie
        $userS = User::create([
            'name' => 'salarie',
            'email' => 'salarie@example.com',
            'password' => bcrypt('salarie'),
        ]);

        $userS->assignRole('salarie');

    }
}
