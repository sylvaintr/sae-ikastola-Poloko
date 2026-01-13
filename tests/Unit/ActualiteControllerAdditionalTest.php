<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\ActualiteController;
use App\Models\Actualite;
use App\Models\Etiquette;

class ActualiteControllerAdditionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_appel_methode_indefinie_lance_bad_method_call_exception()
    {
        $this->expectException(\BadMethodCallException::class);

        $controller = new ActualiteController();
        // Call a method that does not exist and is not handled by helpers
        $controller->thisMethodDoesNotExistAtAll();
    }

    public function test_filtre_donnees_etiquettes_whereHas_restreint_resultats()
    {
        // Create an etiquette and an actualite that references it
        $et = Etiquette::factory()->create(['nom' => 'special-tag']);
        $actWith = Actualite::factory()->create(['titrefr' => 'WithTag', 'archive' => false]);
        $actWithout = Actualite::factory()->create(['titrefr' => 'WithoutTag', 'archive' => false]);
        $actWith->etiquettes()->attach($et->idEtiquette);

        $controller = new ActualiteController();

        // Prepare a query and apply the filter callable that DataTables would call
        $query = Actualite::query();
        // call the filter helper via controller (delegated to ActualiteHelpers)
        $controller->filterColumnEtiquettesCallback($query, 'special');

        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('WithTag', $results->first()->titrefr);
    }

    public function test_endpoint_data_declenche_filtres_inline_titre_et_etiquettes()
    {
        // create data
        $et = Etiquette::factory()->create(['nom' => 'special']);
        $a1 = Actualite::factory()->create(['titrefr' => 'UniqueTitle', 'archive' => false, 'dateP' => now()]);
        $a1->etiquettes()->attach($et->idEtiquette);
        Actualite::factory()->create(['titrefr' => 'Other', 'archive' => false, 'dateP' => now()]);

        $controller = new ActualiteController();

        $params = [
            'draw' => 1,
            'columns' => [
                ['data' => 'titre', 'name' => 'titre', 'search' => ['value' => 'Unique']],
                ['data' => 'etiquettes', 'name' => 'etiquettes', 'search' => ['value' => 'special']],
            ],
        ];

        $resp = $controller->data(Request::create('/data', 'GET', $params));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
        $payload = $resp->getData(true);
        $this->assertArrayHasKey('data', $payload);
        // ensure filtered result includes the expected title and etiquettes
        $this->assertNotEmpty($payload['data']);
        $foundTitre = false;
        $foundEtiq = false;
        foreach ($payload['data'] as $row) {
            if (!empty($row['titre']) && str_contains($row['titre'], 'UniqueTitle')) {
                $foundTitre = true;
            }
            if (!empty($row['etiquettes']) && str_contains($row['etiquettes'], 'special')) {
                $foundEtiq = true;
            }
        }

        $this->assertTrue($foundTitre, 'Expected at least one row with titre containing UniqueTitle');
        $this->assertTrue($foundEtiq, 'Expected at least one row with etiquettes containing special');
    }

    public function test_methodes_filtre_inline_sont_callable_et_filtrent_correctement()
    {
        $et = Etiquette::factory()->create(['nom' => 'inline-tag']);
        $a1 = Actualite::factory()->create(['titrefr' => 'InlineMatch', 'archive' => false]);
        $a1->etiquettes()->attach($et->idEtiquette);
        Actualite::factory()->create(['titrefr' => 'NoMatch', 'archive' => false]);

        $controller = new ActualiteController();

        // Call private methods via reflection to ensure the inline logic is covered
        $rClass = new \ReflectionClass($controller);
        $m1 = $rClass->getMethod('filterColumnTitreInline');
        $m1->setAccessible(true);

        $query = Actualite::query();
        $m1->invoke($controller, $query, 'InlineMatch');
        $this->assertCount(1, $query->get());

        $m2 = $rClass->getMethod('filterColumnEtiquettesInline');
        $m2->setAccessible(true);
        $query2 = Actualite::query();
        $m2->invoke($controller, $query2, 'inline-tag');
        $this->assertCount(1, $query2->get());
    }
}
