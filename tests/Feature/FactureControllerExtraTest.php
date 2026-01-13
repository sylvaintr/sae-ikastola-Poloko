<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;
use App\Services\FactureExporter;

class FactureControllerExtraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_export_calls_manual_service_and_returns_binary()
    {
        $facture = Facture::factory()->create(['etat' => 'manuel']);

        // Mock the exporter and bind into container
        $mock = $this->createMock(FactureExporter::class);
        $mock->expects($this->once())->method('serveManualFile')->with($this->isInstanceOf(\App\Models\Facture::class), false)->willReturn('BINARY_CONTENT');
        $this->app->instance(FactureExporter::class, $mock);

        $response = $this->get(route('admin.facture.export', $facture->idFacture));

        $response->assertStatus(200);
        $this->assertEquals('BINARY_CONTENT', $response->getContent());
    }

    public function test_valider_facture_deletes_old_document_files_when_manual()
    {
        Storage::fake('public');

        $facture = Facture::factory()->create(['etat' => 'manuel']);
        $nom = 'factures/facture-' . $facture->idFacture;

        Storage::disk('public')->put($nom . '.docx', 'dummy');
        Storage::disk('public')->put($nom . '.doc', 'dummy2');

        $this->assertTrue(Storage::disk('public')->exists($nom . '.docx'));
        $this->assertTrue(Storage::disk('public')->exists($nom . '.doc'));

        $response = $this->get(route('admin.facture.valider', $facture->idFacture));

        $response->assertRedirect(route('admin.facture.index', $facture->idFacture));
        $this->assertFalse(Storage::disk('public')->exists($nom . '.docx'));
        $this->assertFalse(Storage::disk('public')->exists($nom . '.doc'));

        $this->assertEquals('manuel verifier', Facture::find($facture->idFacture)->etat);
    }

    public function test_update_rejects_invalid_file_by_magic_bytes()
    {
        $facture = Facture::factory()->create();

        $badFile = UploadedFile::fake()->create('bad.doc', 10, 'application/msword');

        $response = $this->put(route('admin.facture.update', $facture->idFacture), [
            'facture' => $badFile,
        ]);

        $response->assertRedirect(route('admin.facture.index'));
        $response->assertSessionHas('error', 'facture.invalidfile');
    }

    public function test_envoyer_facture_attaches_pdf_and_sends_mail_when_verified()
    {
        Mail::fake();
        Storage::fake('public');

        $client = Utilisateur::factory()->create(['email' => 'client@example.org']);
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $famille->utilisateurs()->attach($client->idUtilisateur);

        $facture = Facture::factory()->create(['etat' => 'verifier', 'idFamille' => $famille->idFamille]);

        // Mock exporter to return some pdf binary data
        $mock = $this->createMock(FactureExporter::class);
        $mock->expects($this->once())->method('generateAndServeFacture')->willReturn('%PDF-1.4');
        $this->app->instance(FactureExporter::class, $mock);

        $response = $this->get(route('admin.facture.envoyer', $facture->idFacture));

        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertSent(FactureMail::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });
    }
}
