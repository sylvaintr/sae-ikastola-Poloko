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
        $userCA = Utilisateur::create([
            'prenom' => 'CA',
            'nom' => '',
            'email' => 'ca@example.com',
            'mdp' => bcrypt('CA'),
            'languePref' => 'fr',
            'statutValidation' => true,
        ]);

        $userCA->assignRole('CA');

        $userP = Utilisateur::create([
            'prenom' => 'parent',
            'nom' => '',
            'email' => 'parent@example.com',
            'mdp' => bcrypt('parent'),
            'languePref' => 'fr',
            'statutValidation' => true,
        ]);

        $userP->assignRole('parent');

        // Create a user with role salarie
        $userS = Utilisateur::create([
            'prenom' => 'salarie',
            'nom' => '',
            'email' => 'salarie@example.com',
            'mdp' => bcrypt('salarie'),
            'languePref' => 'fr',
            'statutValidation' => true,
        ]);

        $userS->assignRole('salarie');

    }
}
