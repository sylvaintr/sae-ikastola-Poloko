<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Avoir;
use App\Models\Utilisateur;
use App\Models\Role;

class AvoirTest extends TestCase
{
    use RefreshDatabase;

    public function test_boot_definit_type_modele_lors_creation()
    {
        // given
        $user = Utilisateur::factory()->create();
        $role = Role::factory()->create();

        // when
        $avoir = Avoir::create([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
        ]);

        // then
        $this->assertNotNull($avoir->model_type);
        $this->assertEquals(Utilisateur::class, $avoir->model_type);
    }

    public function test_relations_retournent_les_modeles()
    {
        // given
        $user = Utilisateur::factory()->create();
        $role = Role::factory()->create();

        // when
        $avoir = Avoir::create([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
        ]);

        // then
        $this->assertEquals($user->idUtilisateur, $avoir->utilisateur->idUtilisateur);
        $this->assertEquals($role->idRole, $avoir->role->idRole);
    }
}
