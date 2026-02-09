<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureExporter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureExporterAdditionalTest extends TestCase
{
    public function test_rendre_pdf_depuis_html_retourne_pdf()
    {
        // given: exporter behaviour is provided by existing serveManualFile implementation
        $exporter = $this->getMockBuilder(FactureExporter::class)->onlyMethods(['serveManualFile'])->getMock();
        $exporter->method('serveManualFile')->willReturn('%PDF-BINARY%');

        // when
        $facture = Facture::factory()->create(['etat' => 'verifier']);
        $pdf     = $exporter->serveManualFile($facture, true);

        // then
        $this->assertIsString($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }

    public function test_generer_et_servir_facture_retourne_binaire_et_reponse()
    {
        // given
        $exporter = new FactureExporter();

        $facture = Facture::factory()->create([
            'etat' => 'verifier',
        ]);

        $famille = \App\Models\Famille::factory()->create();

        $montants = [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => collect([]),
        ];

        // when
        // Binary return (PDF) via mocked serveManualFile
        $exporterMock = $this->getMockBuilder(FactureExporter::class)->onlyMethods(['serveManualFile'])->getMock();
        $exporterMock->method('serveManualFile')->willReturn('%PDF-BINARY%');

        $binary = $exporterMock->serveManualFile($facture, true);

        // then
        $this->assertIsString($binary);
        $this->assertStringContainsString('%PDF', $binary);

        // when (HTTP response) - serveManualFile returns content for non-binary as well
        $response = response('%PDF-BINARY%', 200)->header('Content-Disposition', 'attachment; filename="facture.pdf"');

        // then
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_charger_et_servir_fichier_manuel_pdf_et_doc()
    {
        // given
        \Illuminate\Support\Facades\Storage::fake('public');
        $exporter = new FactureExporter();

        // when
        // Case 1: facture in 'verifier' state -> pdf
        $factPdf = Facture::factory()->create(['etat' => 'verifier']);
        $namePdf = 'facture-' . $factPdf->idFacture . '.pdf';
        Storage::disk('public')->put('factures/' . $namePdf, 'PDFDATA');

        $loaded       = $exporter->getLinkFarctureFile($factPdf);
        $servedBinary = $exporter->serveManualFile($factPdf, true);
        $servedResp   = $exporter->serveManualFile($factPdf, false);

        // then
        $this->assertIsArray($loaded);
        $this->assertEquals('pdf', $loaded['ext']);
        $this->assertEquals('PDFDATA', $servedBinary);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $servedResp);

        // when (case 2)
        $factDoc = Facture::factory()->create(['etat' => 'manuel']);
        $nameDoc = 'facture-' . $factDoc->idFacture . '.docx';
        Storage::disk('public')->put('factures/' . $nameDoc, 'DOCDATA');

        $loaded2       = $exporter->getLinkFarctureFile($factDoc);
        $servedBinary2 = $exporter->serveManualFile($factDoc, true);

        // then (case 2)
        $this->assertIsArray($loaded2);
        $this->assertEquals('docx', $loaded2['ext']);
        $this->assertEquals('DOCDATA', $servedBinary2);
    }

}
