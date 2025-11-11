<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;

class FactureControllerTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable route middlewares for these feature tests so we don't need to
        // create authenticated users / roles for every scenario. Tests focus on
        // controller behaviour, not middleware enforcement.
        $this->withoutMiddleware();
    }

    public function test_index_returns_view()
    {
        $response = $this->get(route('admin.facture.index'));

        $response->assertStatus(200);
        $response->assertViewIs('facture.index');
    }

    public function test_show_returns_view_with_data()
    {

        $famille = Famille::factory()->create();
        $facture = Facture::factory()->create(['idFamille' => $famille->idFamille]);
        Enfant::factory()->count(2)->create(['idFamille' => $famille->idFamille]);

        $response = $this->get(route('admin.facture.show', $facture->idFacture));

        $response->assertStatus(200);
        $response->assertViewIs('facture.show');
        $response->assertViewHasAll(['facture', 'famille', 'enfants']);
    }

    public function test_factures_data_returns_json()
    {
        Facture::factory()->count(3)->create();

        $response = $this->getJson(route('admin.factures.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['titre', 'etat', 'actions']
            ]
        ]);
    }

    public function test_export_facture_returns_pdf_or_doc()
    {
        $facture = Facture::factory()->create(['etat' => true]);
        $famille = Famille::factory()->create();
        $facture->idFamille = $famille->id;
        $facture->save();

        $response = $this->get(route('admin.facture.export', $facture->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Test pour un brouillon
        $facture->etat = false;
        $facture->save();

        $response = $this->get(route('admin.facture.export', $facture->id));
        $response->assertHeader('Content-Type', 'application/vnd.ms-word');
    }

    public function test_valider_facture_changes_state()
    {
        $facture = Facture::factory()->create(['etat' => false]);

        $response = $this->get(route('admin.facture.valider', $facture->id));

        $response->assertRedirect(route('admin.facture.index'));
        $this->assertTrue(Facture::find($facture->id)->etat);
    }

    public function test_envoyer_facture_sends_email_when_valid()
    {
        Mail::fake();

        $utilisateur = Utilisateur::factory()->create();
        $facture = Facture::factory()->create([
            'etat' => true,
            'idUtilisateur' => $utilisateur->id
        ]);

        $response = $this->get(route('admin.facture.envoyer', $facture->id));

        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertSent(FactureMail::class, function ($mail) use ($utilisateur) {
            return $mail->hasTo($utilisateur->email);
        });
    }


    public function test_envoyer_facture_does_not_send_email_when_invalid()
    {
        Mail::fake();

        $utilisateur = Utilisateur::factory()->create();
        $facture = Facture::factory()->create([
            'etat' => false,
            'idUtilisateur' => $utilisateur->id
        ]);

        $response = $this->get(route('admin.facture.envoyer', $facture->id));

        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertNothingSent();
    }
}
