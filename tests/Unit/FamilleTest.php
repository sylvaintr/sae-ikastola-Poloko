<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Famille;

class FamilleTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Famille();
        $this->assertInstanceOf(Famille::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Famille())->getTable());
    }
}
