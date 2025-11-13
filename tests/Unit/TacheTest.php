<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tache;

class TacheTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Tache();
        $this->assertInstanceOf(Tache::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Tache())->getTable());
    }
}
