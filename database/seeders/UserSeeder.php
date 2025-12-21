<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Utilisateur;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user with role CA
        $userCA = Utilisateur::firstOrCreate(
            ['email' => 'ca@example.com'],
            [
                'prenom' => 'CA',
                'nom' => 'CA',
                'mdp' => bcrypt('CA'),
                'languePref' => 'fr',
                'statutValidation' => true,
            ]
        );

        $userCA->assignRole('CA');

        $userP = Utilisateur::firstOrCreate(
            ['email' => 'parent@example.com'],
            [
                'prenom' => 'parent',
                'nom' => 'parent',
                'mdp' => bcrypt('parent'),
                'languePref' => 'fr',
                'statutValidation' => true,
            ]
        );

        $userP->assignRole('parent');

        // Create a user with role salarie
        $userS = Utilisateur::firstOrCreate(
            ['email' => 'salarie@example.com'],
            [
                'prenom' => 'salarie',
                'nom' => 'salarie',
                'mdp' => bcrypt('salarie'),
                'languePref' => 'fr',
                'statutValidation' => true,
            ]
        );

        $userS->assignRole('salarie');

    }
}
