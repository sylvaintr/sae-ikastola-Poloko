<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Joindre;

class JoindreTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Joindre();
        $this->assertInstanceOf(Joindre::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Joindre())->getTable());
    }
}
