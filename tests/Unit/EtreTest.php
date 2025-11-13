<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Etre;

class EtreTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Etre();
        $this->assertInstanceOf(Etre::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Etre())->getTable());
    }
}
