<?php

namespace Database\Factories;

use App\Models\Tache;
use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

class TacheFactory extends Factory
{
    protected $model = Tache::class;

    public function definition()
    {
        return [
            'idTache' => $this->faker->unique()->numberBetween(1, 100000),
            'titre' => $this->faker->words(2, true),
            'description' => $this->faker->text(80),
            'type' => $this->faker->randomElement(['low', 'medium', 'high']),
            'etat' => $this->faker->randomElement(['todo', 'doing', 'done']),
            'dateD' => $this->faker->optional()->date(),
            'dateF' => $this->faker->optional()->date(),
            'montantP' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'montantR' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'idEvenement' => Evenement::factory(),
        ];
    }
}
