<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Classe;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classe>
 */
class ClasseFactory extends Factory
{
    protected $model = Classe::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Classe primary key is non-incrementing; generate a unique id
            'idClasse' => $this->faker->unique()->numberBetween(100, 99999),
            'nom' => $this->faker->word(),
            'niveau' => $this->faker->randomElements(['PS', 'MS', 'GS', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'], 1)[0],
        ];
    }
}
