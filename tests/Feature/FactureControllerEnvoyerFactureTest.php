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
        // Ne pas utiliser Storage::fake car le service utilise le vrai storage

        $uniqueEmail = 'client_' . uniqid() . '@example.org';
        $client = Utilisateur::factory()->create(['email' => $uniqueEmail]);
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $famille->utilisateurs()->attach($client->idUtilisateur, ['parite' => 50]);
        // Créer un enfant pour que le calculateur ne retourne pas de redirection
        \App\Models\Enfant::factory()->create(['idFamille' => $famille->idFamille]);

        $facture = Facture::factory()->create([
            'etat' => 'verifier',
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        // Créer un fichier PDF pour que le service le trouve
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', '%PDF-1.4 dummy');

        // perform the request
        $response = $this->get(route('admin.facture.envoyer', $facture->idFacture));

        $response->assertRedirect(route('admin.facture.index'));

        Mail::assertSent(FactureMail::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });

        // Cleanup
        Storage::disk('public')->delete('factures/facture-' . $facture->idFacture . '.pdf');
    }
}
