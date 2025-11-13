<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Activite;

class ActiviteTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Activite();
        $this->assertInstanceOf(Activite::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Activite())->getTable());
    }
}
