<?php
namespace Tests\Unit;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

class DocumentModelTest extends TestCase
{
    public function test_relations_retournent_des_instances_relation()
    {
        // given
        $d = new Document();

        // when
        $utilRel                = $d->utilisateurs();
        $tacheRel               = $d->tache();
        $actualitesRel          = $d->actualites();
        $documentObligatoireRel = $d->documentObligatoire();

        // then
        $this->assertInstanceOf(Relation::class, $utilRel);
        $this->assertInstanceOf(Relation::class, $tacheRel);
        $this->assertInstanceOf(Relation::class, $actualitesRel);
        $this->assertInstanceOf(Relation::class, $documentObligatoireRel);
    }
}
