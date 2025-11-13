<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Classe;

class ClasseTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Classe();
        $this->assertInstanceOf(Classe::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Classe())->getTable());
    }
}
