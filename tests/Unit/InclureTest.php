<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Inclure;

class InclureTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Inclure();
        $this->assertInstanceOf(Inclure::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Inclure())->getTable());
    }
}
