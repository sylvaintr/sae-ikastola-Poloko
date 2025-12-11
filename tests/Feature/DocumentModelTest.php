<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Document;

class DocumentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_factory_creates_record()
    {
        $doc = Document::factory()->create();

        $this->assertDatabaseHas('document', ['idDocument' => $doc->idDocument]);
        $this->assertGreaterThanOrEqual(0,  $doc->utilisateurs()->count());
        $this->assertGreaterThanOrEqual(0,  $doc->actualites()->count());
    }
}
