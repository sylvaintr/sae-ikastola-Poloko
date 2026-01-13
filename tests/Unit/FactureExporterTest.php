<?php

namespace Tests\Unit;

use App\Services\FactureExporter;
use App\Models\Facture;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

class FactureExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_type_contenu_pour_extension()
    {
        // given
        $e = new FactureExporter();

        // when
        $pdfType = $e->contentTypeForExt('pdf');
        $docType = $e->contentTypeForExt('doc');

        // then
        $this->assertSame('application/pdf', $pdfType);
        $this->assertSame('application/vnd.ms-word', $docType);
    }

    public function test_charger_et_servir_fichier_manuel_avec_storage_simple()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'verifier']);
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', 'PDFDATA');
        $exporter = new FactureExporter();

        // when
        $loaded = $exporter->loadManualFile($facture);
        $binary = $exporter->serveManualFile($facture, true);
        $response = $exporter->serveManualFile($facture, false);

        // then
        $this->assertIsArray($loaded);
        $this->assertSame('pdf', $loaded['ext']);
        $this->assertSame('PDFDATA', $binary);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('facture-' . $facture->idFacture . '.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_charger_fichier_manuel_retourne_null_quand_manquant_et_tableau_quand_present()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'manuel']);
        $exporter = new FactureExporter();

        // when / then: missing file => null
        $this->assertNull($exporter->loadManualFile($facture));

        // given: file exists
        $path = 'factures/facture-' . $facture->idFacture . '.docx';
        Storage::disk('public')->put($path, 'DOCDATA');

        // when
        $arr = $exporter->loadManualFile($facture);

        // then
        $this->assertIsArray($arr);
        $this->assertEquals('DOCDATA', $arr['content']);
        $this->assertEquals('docx', $arr['ext']);
    }

    public function test_servir_fichier_manuel_retourne_binaire_et_reponse()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'manuel']);
        $path = 'factures/facture-' . $facture->idFacture . '.docx';
        Storage::disk('public')->put($path, 'DOCDATA');
        $exporter = new FactureExporter();

        // when
        $bin = $exporter->serveManualFile($facture, true);
        $resp = $exporter->serveManualFile($facture, false);

        // then
        $this->assertIsString($bin);
        $this->assertEquals('DOCDATA', $bin);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals('application/vnd.ms-word', $resp->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="facture-', $resp->headers->get('Content-Disposition'));
    }

    public function test_generer_et_servir_facture_respecte_chemins_pdf_et_doc()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'verifier']);
        $montants = [
            'facture' => $facture,
            'famille' => null,
            'enfants' => [],
            'montantcotisation' => 0,
            'montantparticipation' => 0,
            'montangarderie' => 0,
            'montanttotal' => 0,
            'totalPrevisionnel' => 0,
        ];

        // Partial mock the exporter to avoid heavy Dompdf internals
        $exporter = $this->getMockBuilder(FactureExporter::class)->onlyMethods(['renderHtml', 'renderPdfFromHtml'])->getMock();
        $exporter->method('renderHtml')->willReturn('<html>ok</html>');
        $exporter->method('renderPdfFromHtml')->willReturn('%PDF-BINARY%');

        // when: PDF state
        $resp = $exporter->generateAndServeFacture($montants, $facture, false);

        // then
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals('application/pdf', $resp->headers->get('Content-Type'));

        // given: change to doc state
        $facture->etat = 'brouillon';
        $facture->save();

        // when: doc state
        $resp2 = $exporter->generateAndServeFacture($montants, $facture, false);

        // then
        $this->assertInstanceOf(Response::class, $resp2);
        $this->assertEquals('application/vnd.ms-word', $resp2->headers->get('Content-Type'));

        // when: request binary
        $bin = $exporter->generateAndServeFacture($montants, $facture, true);

        // then
        $this->assertIsString($bin);
    }

    public function test_rendre_pdf_depuis_html_retourne_octets()
    {
        // given
        $exporter = new FactureExporter();

        // when
        $pdf = $exporter->renderPdfFromHtml('<html><body><p>ok</p></body></html>');

        // then
        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
    }
}
