<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Correspondre;

class CorrespondreTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Correspondre();
        $this->assertInstanceOf(Correspondre::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Correspondre())->getTable());
    }
}
