<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Lier;

class LierTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Lier();
        $this->assertInstanceOf(Lier::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Lier())->getTable());
    }
}
