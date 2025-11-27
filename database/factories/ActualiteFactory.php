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
        return [
            'idActualite' => $this->faker->unique()->numberBetween(1, 100000),
            'titrefr' => $this->faker->words(2, true),
            'titreeus' => $this->faker->words(2, true),
            'descriptionfr' => $this->faker->text(100),
            'descriptioneus' => $this->faker->text(100),
            'contenufr' => $this->faker->paragraphs(3, true),
            'contenueus' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement(['public', 'privé']),
            'dateP' => $this->faker->date(),
            'archive' => $this->faker->boolean(),
            'lien' => $this->faker->optional()->url(),
            'idUtilisateur' => Utilisateur::factory(),
        ];
    }
}
