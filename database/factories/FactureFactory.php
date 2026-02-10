<?php

namespace Database\Factories;

use App\Models\Facture;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Utilisateur;
use App\Models\Famille;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Facture>
 */
class FactureFactory extends Factory
{
    protected $model = Facture::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'etat' => $this->faker->randomElement([ 'brouillon', 'verifier']),
            'dateC' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'previsionnel' => $this->faker->boolean(),
            'idUtilisateur' => Utilisateur::factory(),
            'idFamille' => Famille::factory(),
        ];
    }
}
