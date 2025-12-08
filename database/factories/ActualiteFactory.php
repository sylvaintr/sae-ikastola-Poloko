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
            'titre' => substr($this->faker->sentence(3),0,30),
            'description' => substr($this->faker->sentence(10),0,100),
            'contenu' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement(['Privée', 'Publique']),
            'dateP' => now(),
            'archive' => $this->faker->boolean,
            'lien' => $this->faker->url(),
            'idUtilisateur' => $this->faker->numberBetween(1, 3),
        ];
    }
}
