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
            'titre' => $this->faker->optional()->sentence(3),
            'description' => $this->faker->text(80),
            'type' => $this->faker->word(),
            'dateP' => $this->faker->date(),
            'archive' => $this->faker->boolean(),
            'lien' => $this->faker->optional()->url(),
            'idUtilisateur' => Utilisateur::factory(),
        ];
    }
}
