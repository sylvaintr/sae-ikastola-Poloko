<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Avoir;
use App\Models\Utilisateur;
use App\Models\Role;

class AvoirModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_boot_definit_type_modele_lors_creation()
    {
        // given
        // none

        // when

        // then
        $user = Utilisateur::factory()->create();
        $role = Role::factory()->create();

        $avoir = Avoir::create([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
        ]);

        $this->assertNotNull($avoir);
        $this->assertEquals(Utilisateur::class, $avoir->model_type);
    }
}
