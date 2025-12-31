<?php

namespace Database\Factories;

use App\Models\Materiel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterielFactory extends Factory
{
    protected $model = Materiel::class;

    public function definition()
    {
        return [
            'idMateriel' => $this->faker->unique()->numberBetween(1, 100000),
            'provenance' => $this->faker->word(),
            'description' => $this->faker->text(80),
        ];
    }
}
