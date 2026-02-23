<?php

namespace Database\Factories;

use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvenementFactory extends Factory
{
    protected $model = Evenement::class;

    public function definition()
    {
        $startAt = $this->faker->dateTimeBetween('now', '+1 month');
        $endAt = $this->faker->optional(0.7)->dateTimeBetween($startAt, '+2 months');

        return [
            'idEvenement' => $this->faker->unique()->numberBetween(1, 100000),
            'titre' => $this->faker->words(3, true),
            'description' => $this->faker->text(80),
            'obligatoire' => $this->faker->boolean(),
            'start_at' => $startAt,
            'end_at' => $endAt,
        ];
    }

    /**
     * Événement obligatoire
     */
    public function obligatoire(): static
    {
        return $this->state(fn(array $attributes) => [
            'obligatoire' => true,
        ]);
    }

    /**
     * Événement optionnel
     */
    public function optionnel(): static
    {
        return $this->state(fn(array $attributes) => [
            'obligatoire' => false,
        ]);
    }

    /**
     * Événement sur une journée entière
     */
    public function allDay(): static
    {
        $date = $this->faker->dateTimeBetween('now', '+1 month');
        return $this->state(fn(array $attributes) => [
            'start_at' => $date->format('Y-m-d') . ' 00:00:00',
            'end_at' => null,
        ]);
    }
}
