<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Recette;

class RecetteTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Recette();
        $this->assertInstanceOf(Recette::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Recette())->getTable());
    }
}
