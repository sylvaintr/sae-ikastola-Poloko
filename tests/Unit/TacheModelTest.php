<?php

namespace Tests\Unit;

use App\Models\Tache;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

class TacheModelTest extends TestCase
{
    public function test_relations_retournent_instances_relation()
    {
        // given
        $t = new Tache();

        // when / then
        $this->assertInstanceOf(Relation::class, $t->evenement()); // then
        $this->assertInstanceOf(Relation::class, $t->realisateurs()); // then
        $this->assertInstanceOf(Relation::class, $t->documents()); // then
        $this->assertInstanceOf(Relation::class, $t->historiques()); // then
    }
}
