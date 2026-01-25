<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FactureExporter;
use App\Models\Facture;
use Illuminate\Support\Facades\Storage;

class FactureExporterAdditionalTest extends TestCase
{
    public function test_rendre_pdf_depuis_html_retourne_pdf()
    {
        // given
        $exporter = new FactureExporter();
        $html = '<html><body><p>Hello PDF</p></body></html>';

        // when
        $pdf = $exporter->renderPdfFromHtml($html);

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
        // Binary return (PDF)
        $binary = $exporter->generateAndServeFacture($montants, $facture, true);

        // then
        $this->assertIsString($binary);
        $this->assertStringContainsString('%PDF', $binary);

        // when (HTTP response)
        $response = $exporter->generateAndServeFacture($montants, $facture, false);

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

        $loaded = $exporter->loadManualFile($factPdf);
        $servedBinary = $exporter->serveManualFile($factPdf, true);
        $servedResp = $exporter->serveManualFile($factPdf, false);

        // then
        $this->assertIsArray($loaded);
        $this->assertEquals('pdf', $loaded['ext']);
        $this->assertEquals('PDFDATA', $servedBinary);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $servedResp);

        // when (case 2)
        $factDoc = Facture::factory()->create(['etat' => 'manuel']);
        $nameDoc = 'facture-' . $factDoc->idFacture . '.docx';
        Storage::disk('public')->put('factures/' . $nameDoc, 'DOCDATA');

        $loaded2 = $exporter->loadManualFile($factDoc);
        $servedBinary2 = $exporter->serveManualFile($factDoc, true);

        // then (case 2)
        $this->assertIsArray($loaded2);
        $this->assertEquals('docx', $loaded2['ext']);
        $this->assertEquals('DOCDATA', $servedBinary2);
    }

    public function test_type_contenu_pour_extension_supplementaire()
    {
        // given
        $exporter = new FactureExporter();

        // when
        $ctPdf = $exporter->contentTypeForExt('pdf');
        $ctDocx = $exporter->contentTypeForExt('docx');

        // then
        $this->assertEquals('application/pdf', $ctPdf);
        $this->assertEquals('application/vnd.ms-word', $ctDocx);
    }
}
