<?php
namespace Database\Factories;

use App\Models\Tache;
use App\Models\TacheHistorique;
use Illuminate\Database\Eloquent\Factories\Factory;

class TacheHistoriqueFactory extends Factory
{
    protected $model = TacheHistorique::class;

    public function definition()
    {
        return [
            'idTache'     => Tache::factory(),
            'statut'      => $this->faker->randomElement(['ouverte', 'en cours', 'fermée']),
            'titre'       => $this->faker->words(3, true),
            'urgence'     => $this->faker->randomElement(['faible', 'moyenne', 'haute']),
            'description' => $this->faker->optional()->text(100),
            'modifie_par' => null,
        ];
    }
}
