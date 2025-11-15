<?php

namespace Database\Factories;

use App\Models\Etiquette;
use Illuminate\Database\Eloquent\Factories\Factory;

class EtiquetteFactory extends Factory
{
    protected $model = Etiquette::class;

    public function definition()
    {
        return [
            'idEtiquette' => $this->faker->unique()->numberBetween(1, 100000),
            'nom' => $this->faker->word(),
        ];
    }
}
