<?php

namespace Database\Factories;

use App\Models\Activite;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    public function definition()
    {
        return [
            'activite' => $this->faker->word(),
            'dateP' => $this->faker->date(),
        ];
    }
}
