<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Document;

class DocumentTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Document();
        $this->assertInstanceOf(Document::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Document())->getTable());
    }
}
