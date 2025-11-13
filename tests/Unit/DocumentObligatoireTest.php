<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DocumentObligatoire;

class DocumentObligatoireTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new DocumentObligatoire();
        $this->assertInstanceOf(DocumentObligatoire::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new DocumentObligatoire())->getTable());
    }
}
