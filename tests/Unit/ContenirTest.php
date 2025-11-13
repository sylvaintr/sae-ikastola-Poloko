<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Contenir;

class ContenirTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Contenir();
        $this->assertInstanceOf(Contenir::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Contenir())->getTable());
    }
}
