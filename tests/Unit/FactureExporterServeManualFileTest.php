<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureExporterServeManualFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_serveManualFile_returns_binary_when_requested()
    {
        Storage::fake('public');

        $facture            = new Facture();
        $facture->idFacture = 8888;
        $facture->etat      = 'draft';

        Storage::disk('public')->put('factures/facture-8888.docx', 'DOCX_CONTENT');

        $svc = app()->make('App\Services\FactureExporter');

        $res = $svc->serveManualFile($facture, true);

        $this->assertIsString($res);
        $this->assertStringContainsString('DOCX_CONTENT', $res);
    }

    public function test_serveManualFile_returns_response_with_headers()
    {
        Storage::fake('public');

        $facture            = new Facture();
        $facture->idFacture = 9999;
        $facture->etat      = 'verifier';

        Storage::disk('public')->put('factures/facture-9999.pdf', 'PDF_BYTES');

        $svc = app()->make('App\Services\FactureExporter');
        $res = $svc->serveManualFile($facture, false);

        $this->assertInstanceOf(\Illuminate\Http\Response::class, $res);
        $this->assertEquals('application/pdf', $res->headers->get('Content-Type'));
    }

    public function test_servir_fichier_manuel_retourne_null_quand_aucun_fichier_manuel()
    {
        Storage::fake('public');

        $facture = Facture::factory()->create(['etat' => 'manuel']);

        $exporter = new FactureExporter();

        $resultBinary   = $exporter->serveManualFile($facture, true);
        $resultResponse = $exporter->serveManualFile($facture, false);

        $this->assertNull($resultBinary);
        $this->assertNull($resultResponse);
    }
}
