<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Avoir;

class AvoirTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Avoir();
        $this->assertInstanceOf(Avoir::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Avoir())->getTable());
    }
}
