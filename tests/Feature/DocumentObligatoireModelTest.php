<?php
namespace Tests\Feature;

use App\Models\DocumentObligatoire;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $this->assertGreaterThanOrEqual(0, $documentObligatoire->roles()->count());
    }
    public function test_relations_retournent_des_instances_relation()
    {
        // given
        $d = new DocumentObligatoire();

        // when
        $rolesRel    = $d->roles();
        $documentRel = $d->documents();

        // then
        $this->assertInstanceOf(Relation::class, $rolesRel);
        $this->assertInstanceOf(Relation::class, $documentRel);

    }
}
