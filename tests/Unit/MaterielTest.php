<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Materiel;

class MaterielTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Materiel();
        $this->assertInstanceOf(Materiel::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Materiel())->getTable());
    }
}
