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

    public function test_envoyerFacture_attaches_pdf_and_sends_mail()
    {
        Mail::fake();
        Storage::fake('public');

        $client = Utilisateur::factory()->create(['email' => 'client@example.org']);
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $famille->utilisateurs()->attach($client->idUtilisateur);

        $facture = Facture::factory()->create([
            'etat' => 'verifier',
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        // Mock exporter to return pdf bytes and expect to be called with $facture and true
        $mock = $this->createMock(FactureExporter::class);
        $mock->expects($this->once())
            ->method('serveManualFile')
            ->with($this->isInstanceOf(Facture::class), true)
            ->willReturn('%PDF-1.4');
        $this->app->instance(FactureExporter::class, $mock);

        // perform the request
        $response = $this->get(route('admin.facture.envoyer', $facture->idFacture));

        $response->assertRedirect(route('admin.facture.index'));

        Mail::assertSent(FactureMail::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });
    }
}
