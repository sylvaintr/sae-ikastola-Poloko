<?php

namespace Database\Seeders;

use App\Models\Actualite;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActualiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Actualite::create([
            'idActualite' => 1,
            'titre' => 'Albisteen gehikuntza',
            'description' => 'Raphaël Audouard, BUT informatika 3. ikasturteko ikasleak, berrien zerrenda gehitu du',
            'type' => 'Privée',
            'dateP' => '2025-11-05',
            'archive' => 0,
            'lien' => null,
            'idUtilisateur' => 0,
        ]);
        Actualite::create([
            'idActualite' => 2,
            'titre' => 'Actu privée ou publique ?',
            'description' => 'Désormais, si une actualité est privée, elle ne sera plus affichée à l\'accueil.',
            'type' => 'Publique',
            'dateP' => '2025-11-05',
            'archive' => 0,
            'lien' => null,
            'idUtilisateur' => 0,
        ]);
    }
}
