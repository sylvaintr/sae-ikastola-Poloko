<?php

namespace Database\Factories;

use App\Models\Recette;
use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecetteFactory extends Factory
{
    protected $model = Recette::class;

    public function definition()
    {
        return [
            'idRecette' => $this->faker->unique()->numberBetween(1, 100000),
            'description' => $this->faker->text(60),
            'prix' => (string) $this->faker->randomFloat(2, 1, 100),
            'quantite' => (string) $this->faker->numberBetween(1, 20),
            'idEvenement' => Evenement::factory(),
        ];
    }
}
