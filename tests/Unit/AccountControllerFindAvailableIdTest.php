<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Http\Controllers\Admin\AccountController;

class AccountControllerFindAvailableIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_trouver_id_disponible_sur_table_vide_retourne_un()
    {
        // given
        // ensure table is empty
        Utilisateur::query()->delete();

        $controller = new AccountController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');

        // when
        $id = $ref->invoke($controller);

        // then
        $this->assertEquals(1, $id);
    }

    public function test_trouver_id_disponible_trouve_un_ecart()
    {
        // given
        Utilisateur::query()->delete();
        Utilisateur::factory()->create(['idUtilisateur' => 1]);
        Utilisateur::factory()->create(['idUtilisateur' => 2]);
        Utilisateur::factory()->create(['idUtilisateur' => 4]);

        $controller = new AccountController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');

        // when
        $id = $ref->invoke($controller);

        // then
        $this->assertEquals(3, $id);
    }

    public function test_trouver_id_disponible_retourne_max_plus_un_quand_pas_de_vide()
    {
        // given
        Utilisateur::query()->delete();
        for ($i = 1; $i <= 3; $i++) {
            Utilisateur::factory()->create(['idUtilisateur' => $i]);
        }

        $controller = new AccountController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');

        // when
        $id = $ref->invoke($controller);

        // then
        $this->assertEquals(4, $id);
    }
}
