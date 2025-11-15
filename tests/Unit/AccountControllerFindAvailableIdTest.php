<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;
use App\Http\Controllers\Admin\AccountController;

class AccountControllerFindAvailableIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_available_id_on_empty_returns_one()
    {
        // ensure table is empty
        Utilisateur::query()->delete();

        $controller = new AccountController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');



        $id = $ref->invoke($controller);
        $this->assertEquals(1, $id);
    }

    public function test_find_available_id_finds_gap()
    {
        Utilisateur::query()->delete();
        Utilisateur::factory()->create(['idUtilisateur' => 1]);
        Utilisateur::factory()->create(['idUtilisateur' => 2]);
        Utilisateur::factory()->create(['idUtilisateur' => 4]);

        $controller = new AccountController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');

        $id = $ref->invoke($controller);
        $this->assertEquals(3, $id);
    }

    public function test_find_available_id_returns_max_plus_one_when_no_gaps()
    {
        Utilisateur::query()->delete();
        for ($i = 1; $i <= 3; $i++) {
            Utilisateur::factory()->create(['idUtilisateur' => $i]);
        }

        $controller = new AccountController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');


        $id = $ref->invoke($controller);
        $this->assertEquals(4, $id);
    }
}
