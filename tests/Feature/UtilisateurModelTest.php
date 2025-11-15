<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Models\Famille;

class UtilisateurModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilisateur_factory_and_family_pivot()
    {
        $user = Utilisateur::factory()->create();

        $this->assertDatabaseHas('utilisateur', ['email' => $user->email]);

        // Create a family and attach the user to it via pivot
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 'parent']);

        $this->assertDatabaseHas('lier', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $user->idUtilisateur,
        ]);
        $this->assertGreaterThanOrEqual(0,  $user->actualites()->count());
        $this->assertGreaterThanOrEqual(0,  $user->documents()->count());
        $this->assertGreaterThanOrEqual(0,  $user->factures()->count());
        $this->assertGreaterThanOrEqual(0,  $user->avoirs()->count());
        $this->assertGreaterThanOrEqual(0,  $user->tachesRealisees()->count());
        $this->assertGreaterThanOrEqual(0,  $user->familles()->count());
        $this->assertGreaterThanOrEqual(0,  $user->rolesCustom()->count());
    }
}
