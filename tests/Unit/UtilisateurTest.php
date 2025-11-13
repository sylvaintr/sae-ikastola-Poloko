<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Utilisateur;

class UtilisateurTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Utilisateur();
        $this->assertInstanceOf(Utilisateur::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Utilisateur())->getTable());
    }
}
