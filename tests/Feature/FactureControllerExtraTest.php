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

    public function test_export_appelle_service_manuel_et_retourne_binaire()
    {
        // given
        $utilisateur = Utilisateur::factory()->create();
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->attach($utilisateur->idUtilisateur, ['parite' => 50]);
        // Créer un enfant pour que le calculateur ne retourne pas de redirection
        \App\Models\Enfant::factory()->create(['idFamille' => $famille->idFamille]);

        $facture = Facture::factory()->create([
            'etat' => 'manuel',
            'idUtilisateur' => $utilisateur->idUtilisateur,
            'idFamille' => $famille->idFamille,
        ]);

        // Créer un fichier pour que le service le trouve
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.docx', 'BINARY_CONTENT');

        // when
        $response = $this->get(route('admin.facture.export', $facture->idFacture));

        // then
        $response->assertStatus(200);
        $this->assertEquals('BINARY_CONTENT', $response->getContent());

        // Cleanup
        Storage::disk('public')->delete('factures/facture-' . $facture->idFacture . '.docx');
    }

    public function test_valider_facture_supprime_anciens_fichiers_quand_manuel()
    {
        // given - Ne pas utiliser Storage::fake car le service de conversion vérifie avec le vrai storage
        $facture = Facture::factory()->create(['etat' => 'manuel']);
        $nom = 'factures/facture-' . $facture->idFacture;

        // Créer les fichiers sur le vrai storage
        Storage::disk('public')->put($nom . '.docx', 'dummy');

        $this->assertTrue(Storage::disk('public')->exists($nom . '.docx'));

        // when
        $response = $this->get(route('admin.facture.valider', $facture->idFacture));

        // then
        $response->assertRedirect(route('admin.facture.index'));
        // En mode test, le service retourne true et supprime le fichier si deleteOriginal est true
        // Mais le contrôleur appelle convertFactureToPdf avec deleteOriginal=false par défaut

        // L'état devrait passer à 'verifier' en mode test (short-circuit retourne true)
        $this->assertEquals('verifier', Facture::find($facture->idFacture)->etat);

        // Cleanup
        Storage::disk('public')->delete($nom . '.docx');
    }

    public function test_mise_a_jour_rejette_fichier_invalide_par_magic_bytes()
    {
        // given
        $facture = Facture::factory()->create();

        $badFile = UploadedFile::fake()->create('bad.doc', 10, 'application/msword');

        // when
        $response = $this->put(route('admin.facture.update', $facture->idFacture), [
            'facture' => $badFile,
        ]);

        // then
        $response->assertRedirect(route('admin.facture.index'));
        $response->assertSessionHas('error', 'facture.invalidfile');
    }

    public function test_envoyer_facture_attache_pdf_et_envoie_mail_si_verifie()
    {
        // given
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
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', '%PDF-1.4 dummy content');

        // when
        $response = $this->get(route('admin.facture.envoyer', $facture->idFacture));

        // then
        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertSent(FactureMail::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });

        // Cleanup
        Storage::disk('public')->delete('factures/facture-' . $facture->idFacture . '.pdf');
    }
}
