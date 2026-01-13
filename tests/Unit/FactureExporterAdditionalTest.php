<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FactureExporter;
use App\Models\Facture;
use Illuminate\Support\Facades\Storage;

class FactureExporterAdditionalTest extends TestCase
{
    public function test_render_pdf_from_html_returns_pdf()
    {
        $exporter = new FactureExporter();
        $html = '<html><body><p>Hello PDF</p></body></html>';

        $pdf = $exporter->renderPdfFromHtml($html);

        $this->assertIsString($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }

    public function test_generate_and_serve_facture_returns_binary_and_response()
    {
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

        // Binary return (PDF)
        $binary = $exporter->generateAndServeFacture($montants, $facture, true);
        $this->assertIsString($binary);
        $this->assertStringContainsString('%PDF', $binary);

        // HTTP Response return
        $response = $exporter->generateAndServeFacture($montants, $facture, false);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_load_and_serve_manual_file_pdf_and_doc()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $exporter = new FactureExporter();

        // Case 1: facture in 'verifier' state -> pdf
        $factPdf = Facture::factory()->create(['etat' => 'verifier']);
        $namePdf = 'facture-' . $factPdf->idFacture . '.pdf';
        Storage::disk('public')->put('factures/' . $namePdf, 'PDFDATA');

        $loaded = $exporter->loadManualFile($factPdf);
        $this->assertIsArray($loaded);
        $this->assertEquals('pdf', $loaded['ext']);

        $servedBinary = $exporter->serveManualFile($factPdf, true);
        $this->assertEquals('PDFDATA', $servedBinary);

        $servedResp = $exporter->serveManualFile($factPdf, false);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $servedResp);

        // Case 2: manuel state -> docx
        $factDoc = Facture::factory()->create(['etat' => 'manuel']);
        $nameDoc = 'facture-' . $factDoc->idFacture . '.docx';
        Storage::disk('public')->put('factures/' . $nameDoc, 'DOCDATA');

        $loaded2 = $exporter->loadManualFile($factDoc);
        $this->assertIsArray($loaded2);
        $this->assertEquals('docx', $loaded2['ext']);

        $servedBinary2 = $exporter->serveManualFile($factDoc, true);
        $this->assertEquals('DOCDATA', $servedBinary2);
    }

    public function test_content_type_for_ext()
    {
        $exporter = new FactureExporter();
        $this->assertEquals('application/pdf', $exporter->contentTypeForExt('pdf'));
        $this->assertEquals('application/vnd.ms-word', $exporter->contentTypeForExt('docx'));
    }
}
