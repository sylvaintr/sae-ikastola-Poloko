<?php

namespace Database\Factories;

use App\Models\Famille;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lier;

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
            'idFamille' => Lier::factory()->create()->idFamille,
        ];
    }
}
