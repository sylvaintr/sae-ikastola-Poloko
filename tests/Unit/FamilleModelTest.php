<?php

namespace Tests\Unit;

use App\Models\Famille;
use Tests\TestCase;

class FamilleModelTest extends TestCase
{
    public function test_get_id_attribute()
    {
        // given
        $f = new Famille();
        $f->idFamille = 55;

        // when
        $id = $f->id;

        // then
        $this->assertSame(55, $id);
    }
}
