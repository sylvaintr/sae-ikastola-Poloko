<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_getLinkFarctureFile_returns_array_when_file_exists()
    {
        Storage::disk('public')->put('factures/facture-42.docx', 'docx-content');

        $facture            = new Facture();
        $facture->idFacture = 42;
        $facture->etat      = 'draft';

        $service = new FactureExporter();

        $res = $service->getLinkFarctureFile($facture);

        $this->assertIsArray($res);
        $this->assertEquals('docx', $res['ext']);
        $this->assertEquals('facture-42.docx', $res['filename']);
        $this->assertEquals('docx-content', $res['content']);
    }

    public function test_serveManualFile_returns_binary_or_response_based_on_flag()
    {
        Storage::disk('public')->put('factures/facture-43.pdf', 'pdf-bytes');

        $facture            = new Facture();
        $facture->idFacture = 43;
        $facture->etat      = 'verifier';

        $service = new FactureExporter();

        $binary = $service->serveManualFile($facture, true);
        $this->assertIsString($binary);
        $this->assertEquals('pdf-bytes', $binary);

        $resp = $service->serveManualFile($facture, false);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals('application/pdf', $resp->headers->get('Content-Type'));
        $this->assertStringContainsString('facture-43.pdf', $resp->headers->get('Content-Disposition'));
    }

    public function test_charger_et_servir_un_fichier_manuel_avec_storage_simple()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'verifier']);
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', 'PDFDATA');
        $exporter = new FactureExporter();

        // when
        $loaded   = $exporter->getLinkFarctureFile($facture);
        $binary   = $exporter->serveManualFile($facture, true);
        $response = $exporter->serveManualFile($facture, false);

        // then
        $this->assertIsArray($loaded);
        $this->assertSame('pdf', $loaded['ext']);
        $this->assertSame('PDFDATA', $binary);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('facture-' . $facture->idFacture . '.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_charger_fichier_manuel_retourne_null_quand_manquant_et_tableau_quand_presente()
    {
        // given
        Storage::fake('public');
        $facture  = Facture::factory()->create(['etat' => 'manuel']);
        $exporter = new FactureExporter();

        // when / then: missing file => null
        $this->assertNull($exporter->getLinkFarctureFile($facture));

        // given: file exists
        $path = 'factures/facture-' . $facture->idFacture . '.docx';
        Storage::disk('public')->put($path, 'DOCDATA');

        // when
        $arr = $exporter->getLinkFarctureFile($facture);

        // then
        $this->assertIsArray($arr);
        $this->assertEquals('DOCDATA', $arr['content']);
        $this->assertEquals('docx', $arr['ext']);
    }

    public function test_servir_fichier_manuel_retourne_binaire_et_une_reponse()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'manuel']);
        $path    = 'factures/facture-' . $facture->idFacture . '.docx';
        Storage::disk('public')->put($path, 'DOCDATA');
        $exporter = new FactureExporter();

        // when
        $bin  = $exporter->serveManualFile($facture, true);
        $resp = $exporter->serveManualFile($facture, false);

        // then
        $this->assertIsString($bin);
        $this->assertEquals('DOCDATA', $bin);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $resp->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="facture-', $resp->headers->get('Content-Disposition'));
    }

}
