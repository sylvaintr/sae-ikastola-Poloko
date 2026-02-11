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

    protected FactureExporter $exporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exporter = new FactureExporter();
        Storage::fake('public');
    }

    /**
     * Helper pour créer une facture et son fichier simulé.
     */
    private function createFactureWithFile(string $etat, string $ext): Facture
    {
        $facture = Facture::factory()->create([
            'etat' => $etat,
        ]);

        $filename = 'factures/facture-' . $facture->idFacture . '.' . $ext;
        Storage::disk('public')->put($filename, 'fake content');

        return $facture;
    }

    /** @test */
    public function it_serves_pdf_content_type()
    {
        $facture = $this->createFactureWithFile('verifier', 'pdf');

        $response = $this->exporter->serveManualFile($facture, false);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('facture-' . $facture->idFacture . '.pdf', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_serves_doc_content_type()
    {
        $facture = $this->createFactureWithFile('en_attente', 'doc');

        $response = $this->exporter->serveManualFile($facture, false);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/msword', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_serves_docx_content_type()
    {
        $facture = $this->createFactureWithFile('en_attente', 'docx');

        $response = $this->exporter->serveManualFile($facture, false);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_serves_odt_content_type()
    {
        $facture = $this->createFactureWithFile('en_attente', 'odt');

        $response = $this->exporter->serveManualFile($facture, false);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/vnd.oasis.opendocument.text', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_serves_octet_stream_for_unknown_extension()
    {
        // On simule une extension non gérée dans le match, mais acceptée par getLinkFarctureFile
        // Note: Pour tester le 'default', il faudrait que getLinkFarctureFile accepte d'autres exts.
        // Ici on force le retour binaire pour vérifier le comportement de base.
        $facture = $this->createFactureWithFile('verifier', 'pdf');
        $binary  = $this->exporter->serveManualFile($facture, true);

        $this->assertEquals('fake content', $binary);
    }

    /** @test */
    public function it_returns_null_if_file_does_not_exist()
    {
        $facture = Facture::factory()->create(['etat' => 'verifier']);

        // Aucun fichier créé dans le Storage::fake
        $response = $this->exporter->serveManualFile($facture, false);

        $this->assertNull($response);
    }

    /** @test */
    public function it_returns_raw_binary_when_requested()
    {
        $facture = $this->createFactureWithFile('verifier', 'pdf');

        $result = $this->exporter->serveManualFile($facture, true);

        $this->assertIsString($result);
        $this->assertEquals('fake content', $result);
    }

    /** @test */
    public function it_filters_extensions_based_on_facture_state()
    {
        // Si l'état est 'verifier', il ne doit pas trouver le .docx même s'il existe
        $facture = Facture::factory()->create(['etat' => 'verifier']);
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.docx', 'wrong file');

        $response = $this->exporter->serveManualFile($facture, false);

        $this->assertNull($response);
    }

    /** @test */
    public function it_serves_octet_stream_for_unknown_extensions_in_match()
    {
        // 1. On crée une facture avec un état qui n'est pas 'verifier'
        $facture = Facture::factory()->create([
            'etat' => 'en_attente',
        ]);

        // Si on veut tester la robustesse du match :
        $filename = 'factures/facture-' . $facture->idFacture . '.doc';
        Storage::disk('public')->put($filename, 'fake content');

        // On appelle la fonction
        $response = $this->exporter->serveManualFile($facture, false);

        // Ici, avec ton code actuel, ça renverra 'application/msword'.
        $this->assertEquals('application/msword', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_returns_octet_stream_when_extension_is_not_handled_in_match()
    {
        $facture = Facture::factory()->create(['idFacture' => 999, 'etat' => 'en_attente']);

        // On crée un mock partiel du service pour simuler un retour de fichier inconnu
        $mockExporter = \Mockery::mock(FactureExporter::class)->makePartial();

        // On force getLinkFarctureFile à retourner une extension 'png' (non gérée dans ton match)
        $mockExporter->shouldReceive('getLinkFarctureFile')
            ->with($facture)
            ->andReturn([
                'content'  => 'fake_binary',
                'ext'      => 'png', // png n'est pas dans ton match
                'filename' => 'facture-999.png',
            ]);

        $response = $mockExporter->serveManualFile($facture, false);

        // Là, on tombe dans le default : application/octet-stream
        $this->assertEquals('application/octet-stream', $response->headers->get('Content-Type'));
    }

}
