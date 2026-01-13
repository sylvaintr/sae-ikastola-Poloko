<?php

namespace Tests\Unit;

use App\Models\Facture;
use Tests\TestCase;

class FactureModelTest extends TestCase
{
    public function test_set_etat_with_boolean_and_string()
    {
        // given
        $f = new Facture();

        // when / then: boolean true => 'verifier'
        $f->etat = true; // when
        $this->assertSame('verifier', $f->getAttributes()['etat']); // then

        // when / then: boolean false => 'brouillon'
        $f->etat = false; // when
        $this->assertSame('brouillon', $f->getAttributes()['etat']); // then

        // when / then: explicit string preserved
        $f->etat = 'manuel'; // when
        $this->assertSame('manuel', $f->getAttributes()['etat']); // then

        // when / then: invalid string falls back to 'brouillon'
        $f->etat = 'invalid-state'; // when
        $this->assertSame('brouillon', $f->getAttributes()['etat']); // then
    }

    public function test_get_id_alias()
    {
        // given
        $f = new Facture();
        $f->idFacture = 123;

        // when
        $id = $f->id;

        // then
        $this->assertSame(123, $id);
    }
}
