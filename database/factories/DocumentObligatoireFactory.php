<?php

namespace Database\Factories;

use App\Models\DocumentObligatoire;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentObligatoireFactory extends Factory
{
    protected $model = DocumentObligatoire::class;

    public function definition()
    {
        return [
            'idDocumentObligatoire' => $this->faker->unique()->numberBetween(1, 100000),
            'nom' => $this->faker->optional()->word(),
            'dateE' => $this->faker->optional()->boolean(),
        ];
    }
}
