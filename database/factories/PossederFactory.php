<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Posseder;
use App\Models\Etiquette;
use App\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Posseder>
 */
class PossederFactory extends Factory
{

     protected $model = Posseder::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idEtiquette' => Etiquette::factory(),
            'idRole' => Role::factory(),
        ];
    }
}
