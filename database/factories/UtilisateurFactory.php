<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Utilisateur;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {



        return [
            'prenom' => $this->faker->firstName(),
            'nom' => $this->faker->lastName(),
            // store hashed password in 'mdp' column (model maps password accessor to mdp)
            'mdp' => Hash::make('password'),
            'email' => $this->faker->unique()->safeEmail(),
            // mark email verified by default; tests can call ->unverified() to set null
            'email_verified_at' => now(),
            'languePref' => $this->faker->randomElement(['fr', 'eus']),
            'statutValidation' => true,
            'remember_token' => Str::random(10),
        ];
    }
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
