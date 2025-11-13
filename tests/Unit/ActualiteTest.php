<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Actualite;

class ActualiteTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Actualite();
        $this->assertInstanceOf(Actualite::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Actualite())->getTable());
    }
}
