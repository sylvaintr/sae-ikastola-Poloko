<?php

namespace Database\Factories;

use App\Models\Actualite;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActualiteFactory extends Factory
{
    protected $model = Actualite::class;


    public function definition()
    {
             $faker = \Faker\Factory::create('fr_FR');
        return [
            'idActualite' => $faker->unique()->numberBetween(1, 100000),
            'titrefr' => $faker->words(2, true),
            'titreeus' => $faker->words(2, true),
            'descriptionfr' => $faker->text(100),
            'descriptioneus' => $faker->text(100),
            'contenufr' => $faker->paragraphs(3, true),
            'contenueus' => $faker->paragraphs(3, true),
            'type' => $faker->randomElement(['public', 'privé']),
            'dateP' => $faker->date(),
            'archive' => $faker->boolean(),
            'lien' => $faker->optional()->url(),
            'idUtilisateur' => Utilisateur::factory(),
        ];
    }
}
