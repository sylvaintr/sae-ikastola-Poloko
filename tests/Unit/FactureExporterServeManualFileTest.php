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

    public function test_serveManualFile_returns_null_when_no_manual_file()
    {
        Storage::fake('public');

        $facture = Facture::factory()->create(['etat' => 'manuel']);

        $exporter = new FactureExporter();

        $resultBinary = $exporter->serveManualFile($facture, true);
        $resultResponse = $exporter->serveManualFile($facture, false);

        $this->assertNull($resultBinary);
        $this->assertNull($resultResponse);
    }
}
