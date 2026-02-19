<?php
namespace Database\Factories;

use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enfant>
 */
class EnfantFactory extends Factory
{

    protected $model = Enfant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idEnfant'       => $this->faker->unique()->numberBetween(1, 999999),
            'nom'            => $this->faker->lastName(),
            'prenom'         => $this->faker->firstName(),
            'dateN'          => $this->faker->date(),
            'sexe'           => $this->faker->randomElement(['M', 'F']),
            'NNI'            => $this->faker->unique()->numberBetween(100000000, 999999999),
            'nbFoisGarderie' => $this->faker->numberBetween(0, 15),
            'idClasse'       => Classe::factory(),
        ];
    }
}
