<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Evenement;

class EvenementTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Evenement();
        $this->assertInstanceOf(Evenement::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Evenement())->getTable());
    }
}
