<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Facture;

class FactureModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_facture_factory_creates_relations()
    {
        // given
        // no setup needed

        // when
        $facture = Facture::factory()->create();

        // then
        $this->assertDatabaseHas('facture', ['idFacture' => $facture->idFacture]);

        $this->assertNotNull($facture->famille);
        $this->assertNotNull($facture->utilisateur);
    }
}
