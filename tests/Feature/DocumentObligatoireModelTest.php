<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\DocumentObligatoire;

class DocumentObligatoireModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_obligatoire_factory_creates_record()
    {
        // given
        // none

        // when
        $documentObligatoire = DocumentObligatoire::factory()->create();

        // then
        $this->assertDatabaseHas('documentObligatoire', ['idDocumentObligatoire' => $documentObligatoire->idDocumentObligatoire]);
        $this->assertGreaterThanOrEqual(0,  $documentObligatoire->roles()->count());
    }
}
