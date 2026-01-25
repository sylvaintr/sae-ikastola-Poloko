<?php

namespace Tests\Unit;

use App\Models\Facture;
use Tests\TestCase;

class FactureModelTest extends TestCase
{
    public function test_set_etat_avec_booleen_et_chaine()
    {
        // given
        $f = new Facture();

        // when
        $f->etat = true;
        $val1 = $f->getAttributes()['etat'];

        $f->etat = false;
        $val2 = $f->getAttributes()['etat'];

        $f->etat = 'manuel';
        $val3 = $f->getAttributes()['etat'];

        $f->etat = 'invalid-state';
        $val4 = $f->getAttributes()['etat'];

        // then
        $this->assertSame('verifier', $val1);
        $this->assertSame('brouillon', $val2);
        $this->assertSame('manuel', $val3);
        $this->assertSame('brouillon', $val4);
    }

    public function test_get_alias_id()
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
