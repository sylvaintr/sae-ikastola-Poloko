<?php

namespace Database\Factories;

use App\Models\Famille;
use App\Models\Lier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Famille>
 */
class FamilleFactory extends Factory
{
    protected $model = Famille::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Famille primary key is non-incrementing in the model, generate a unique integer id
            'idFamille' => $this->faker->unique()->numberBetween(1000, 999999),
        ];
    }

    /**
     * After creating a Famille, ensure it has at least one linked Utilisateur via Lier.
     * Many views expect $famille->utilisateurs()->first() to exist.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Famille $famille) {
            Lier::factory()->create(['idFamille' => $famille->idFamille]);
        });
    }
}
