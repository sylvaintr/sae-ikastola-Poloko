<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Enfant;

class EnfantTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Enfant();
        $this->assertInstanceOf(Enfant::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Enfant())->getTable());
    }
}
