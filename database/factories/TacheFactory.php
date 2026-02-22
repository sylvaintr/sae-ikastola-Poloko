<?php

namespace Database\Factories;

use App\Models\Tache;
use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

class TacheFactory extends Factory
{
    protected $model = Tache::class;

    public function definition()
    {
        return [
            'idTache' => $this->faker->unique()->numberBetween(1, 100000),
            'titre' => $this->faker->words(2, true),
            'description' => $this->faker->text(80),
            'type' => 'demande', // Type discriminateur: 'demande' ou 'tache'
            'urgence' => $this->faker->randomElement(['Faible', 'Moyenne', 'Élevée']),
            'etat' => $this->faker->randomElement(['En attente', 'En cours', 'Terminé']),
            'dateD' => $this->faker->optional()->date(),
            'dateF' => $this->faker->optional()->date(),
            'montantP' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'montantR' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'idEvenement' => null, // Par défaut NULL, peut être défini lors de la création
        ];
    }

    /**
     * State for tache type
     */
    public function tache()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'tache',
            ];
        });
    }

    /**
     * State for demande type
     */
    public function demande()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'demande',
            ];
        });
    }
}
