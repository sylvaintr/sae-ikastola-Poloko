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

    public function test_when_exporting_manual_invoice_should_return_binary_content()
    {
        // GIVEN
        $facture = Facture::factory()->create(['etat' => 'manuel']);

        $exporterMock = $this->createMock(FactureExporter::class);
        $exporterMock->expects($this->once())
            ->method('serveManualFile')
            ->with($this->isInstanceOf(Facture::class), false)
            ->willReturn('BINARY_CONTENT');

        $this->app->instance(FactureExporter::class, $exporterMock);

        // WHEN
        $response = $this->get(route('admin.facture.export', $facture->idFacture));

        // THEN
        $response->assertStatus(200);
        $this->assertEquals('BINARY_CONTENT', $response->getContent());
    }

    public function test_when_validating_manual_invoice_should_keep_existing_files()
    {
        // GIVEN
        Storage::fake('public');

        $facture = Facture::factory()->create(['etat' => 'manuel']);
        $baseName = 'factures/facture-' . $facture->idFacture;

        Storage::disk('public')->put($baseName . '.docx', 'dummy');
        Storage::disk('public')->put($baseName . '.doc', 'dummy2');

        $this->assertTrue(Storage::disk('public')->exists($baseName . '.docx'));
        $this->assertTrue(Storage::disk('public')->exists($baseName . '.doc'));

        // WHEN
        $response = $this->get(route('admin.facture.valider', $facture->idFacture));

        // THEN
        $response->assertRedirect(route('admin.facture.index'));
        $this->assertTrue(Storage::disk('public')->exists($baseName . '.docx'));
        $this->assertTrue(Storage::disk('public')->exists($baseName . '.doc'));
        $this->assertEquals('verifier', Facture::find($facture->idFacture)->etat);
    }

    public function test_when_updating_invoice_with_invalid_magic_bytes_should_reject_file()
    {
        // GIVEN
        $facture = Facture::factory()->create();
        $invalidFile = UploadedFile::fake()->create('bad.doc', 10, 'application/msword');

        // WHEN
        $response = $this->put(route('admin.facture.update', $facture->idFacture), [
            'facture' => $invalidFile,
        ]);

        // THEN
        $response->assertRedirect(route('admin.facture.index'));
        $response->assertSessionHas('error', 'facture.invalidfile');
    }

    public function test_when_sending_verified_invoice_should_attach_pdf_and_send_mail()
    {
        // GIVEN
        Mail::fake();
        Storage::fake('public');

        $client = Utilisateur::factory()->create([
            'email' => 'client@example.org'
        ]);

        $famille = Famille::factory()->create();
        $famille->utilisateurs()->sync([$client->idUtilisateur]);

        $facture = Facture::factory()->create([
            'etat' => 'verifier',
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        $exporterMock = $this->createMock(FactureExporter::class);
        $exporterMock->expects($this->once())
            ->method('serveManualFile')
            ->willReturn('%PDF-1.4');

        $this->app->instance(FactureExporter::class, $exporterMock);

        // WHEN
        $response = $this->get(route('admin.facture.envoyer', $facture->idFacture));

        // THEN
        $response->assertRedirect(route('admin.facture.index'));

        Mail::assertSent(FactureMail::class, fn($mail) =>
            $mail->hasTo($client->email)
        );
    }
}
