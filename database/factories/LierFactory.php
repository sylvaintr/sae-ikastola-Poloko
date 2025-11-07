<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use App\Models\Famille;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lier>
 */
class LierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'idUtilisateur' => Utilisateur::factory(),
            'idFamille' => Famille::factory(),
            'parite' => $this->faker->randomElement(['parent', 'tuteur', 'autre', null]),
        ];
    }
}
