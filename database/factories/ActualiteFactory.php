<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Actualite>
 */
class ActualiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'type' => $this->faker->word,
            'dateP' => $this->faker->date,
            'archive' => $this->faker->boolean,
            'lien' => $this->faker->url,
            'idUtilisateur' => $this->faker->numberBetween(1, 50),
        ];
    }
}
