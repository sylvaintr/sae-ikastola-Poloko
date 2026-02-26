<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;
use App\Services\FactureExporter;

class FactureControllerEnvoyerFactureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_when_sending_invoice_should_attach_pdf_and_send_mail()
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
        ->with($this->isInstanceOf(Facture::class), true)
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
