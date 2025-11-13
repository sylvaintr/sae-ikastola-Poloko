<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attribuer;

class AttribuerTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Attribuer();
        $this->assertInstanceOf(Attribuer::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Attribuer())->getTable());
    }
}
