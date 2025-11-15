<?php

namespace Database\Factories;

use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvenementFactory extends Factory
{
    protected $model = Evenement::class;

    public function definition()
    {
        return [
            'idEvenement' => $this->faker->unique()->numberBetween(1, 100000),
            'titre' => $this->faker->words(1, true),
            'description' => $this->faker->text(80),
            'obligatoire' => $this->faker->boolean(),
            'dateE' => $this->faker->date(),
        ];
    }
}
