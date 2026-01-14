<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Attribuer;
use App\Models\Contenir;
use App\Models\Correspondre;
use App\Models\Inclure;
use App\Models\Joindre;
use App\Models\Lier;
use App\Models\Realiser;

class PivotsModelsUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribuer_model_properties_and_relations()
    {
        // given
        $m = new Attribuer();

        // when

        // then
        $this->assertEquals('attribuer', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idRole', $casts);
        $this->assertArrayHasKey('idDocumentObligatoire', $casts);
        $this->assertInstanceOf(BelongsTo::class, $m->role());
        $this->assertInstanceOf(BelongsTo::class, $m->documentObligatoire());
    }

    public function test_contenir_model_properties_and_relations()
    {
        // given
        $m = new Contenir();

        // when

        // then
        $this->assertEquals('contenir', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idUtilisateur', $casts);
        $this->assertArrayHasKey('idDocument', $casts);
        $this->assertInstanceOf(BelongsTo::class, $m->utilisateur());
        $this->assertInstanceOf(BelongsTo::class, $m->document());
    }

    public function test_correspondre_model_properties_and_relations()
    {
        // given
        $m = new Correspondre();

        // when

        // then
        $this->assertEquals('correspondre', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idActualite', $casts);
        $this->assertArrayHasKey('idEtiquette', $casts);
        $this->assertInstanceOf(BelongsTo::class, $m->actualite());
        $this->assertInstanceOf(BelongsTo::class, $m->etiquette());
    }

    public function test_inclure_model_properties_and_relations()
    {
        // given
        $m = new Inclure();

        // when

        // then
        $this->assertEquals('inclure', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idEvenement', $casts);
        $this->assertArrayHasKey('idMateriel', $casts);
        $this->assertInstanceOf(BelongsTo::class, $m->evenement());
        $this->assertInstanceOf(BelongsTo::class, $m->materiel());
    }

    public function test_joindre_model_properties_and_relations()
    {
        // given
        $m = new Joindre();

        // when

        // then
        $this->assertEquals('joindre', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idDocument', $casts);
        $this->assertArrayHasKey('idActualite', $casts);
        $this->assertInstanceOf(BelongsTo::class, $m->document());
        $this->assertInstanceOf(BelongsTo::class, $m->actualite());
    }

    public function test_lier_model_properties_and_relations_and_fillable()
    {
        // given
        $m = new Lier();

        // when

        // then
        $this->assertEquals('lier', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idUtilisateur', $casts);
        $this->assertArrayHasKey('idFamille', $casts);
        $this->assertContains('parite', $m->getFillable());
        $this->assertInstanceOf(BelongsTo::class, $m->utilisateur());
        $this->assertInstanceOf(BelongsTo::class, $m->famille());
    }

    public function test_realiser_model_properties_and_relations_and_fillable()
    {
        // given
        $m = new Realiser();

        // when

        // then
        $this->assertEquals('realiser', $m->getTable());
        $this->assertFalse($m->incrementing);
        $this->assertFalse($m->timestamps);
        $casts = $m->getCasts();
        $this->assertArrayHasKey('idUtilisateur', $casts);
        $this->assertArrayHasKey('idTache', $casts);
        $this->assertArrayHasKey('dateM', $casts);
        $this->assertContains('dateM', $m->getFillable());
        $this->assertContains('description', $m->getFillable());
        $this->assertInstanceOf(BelongsTo::class, $m->utilisateur());
        $this->assertInstanceOf(BelongsTo::class, $m->tache());
    }
}
