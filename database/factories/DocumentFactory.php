<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'idDocument' => $this->faker->unique()->numberBetween(1, 100000),
            'nom' => $this->faker->words(2, true),
            'chemin' => '/storage/docs/' . $this->faker->word() . '.pdf',
            'type' => $this->faker->randomElement(['pdf', 'doc', 'jpg', 'png']),
            'etat' => $this->faker->randomElement(['public', 'private', 'archived']),
        ];
    }
}
