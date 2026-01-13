<?php

namespace Tests\Unit;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

class DocumentModelTest extends TestCase
{
    public function test_relations_return_relation_instances()
    {
        // given
        $d = new Document();

        // when / then
        $this->assertInstanceOf(Relation::class, $d->utilisateurs()); // then
        $this->assertInstanceOf(Relation::class, $d->tache()); // then
        $this->assertInstanceOf(Relation::class, $d->actualites()); // then
    }
}
