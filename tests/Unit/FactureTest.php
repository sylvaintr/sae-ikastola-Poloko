<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Facture;

class FactureTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Facture();
        $this->assertInstanceOf(Facture::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Facture())->getTable());
    }
}
