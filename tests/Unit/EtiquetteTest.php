<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Etiquette;

class EtiquetteTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Etiquette();
        $this->assertInstanceOf(Etiquette::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Etiquette())->getTable());
    }
}
