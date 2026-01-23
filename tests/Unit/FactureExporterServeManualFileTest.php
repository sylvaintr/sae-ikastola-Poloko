<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Services\FactureExporter;
use App\Models\Facture;

class FactureExporterServeManualFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_servir_fichier_manuel_retourne_null_quand_aucun_fichier_manuel()
    {
        // given
        Storage::fake('public');

        $facture = Facture::factory()->create(['etat' => 'manuel']);

        $exporter = new FactureExporter();

        // when
        $resultBinary = $exporter->serveManualFile($facture, true);
        $resultResponse = $exporter->serveManualFile($facture, false);

        // then
        $this->assertNull($resultBinary);
        $this->assertNull($resultResponse);
    }
}
