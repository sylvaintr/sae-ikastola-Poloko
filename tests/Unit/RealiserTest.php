<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Realiser;

class RealiserTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Realiser();
        $this->assertInstanceOf(Realiser::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Realiser())->getTable());
    }
}
