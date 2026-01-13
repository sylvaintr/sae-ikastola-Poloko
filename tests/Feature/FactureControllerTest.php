<?php

namespace Tests\Feature;

use App\Http\Controllers\FactureController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;
use App\Models\Etre;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;
use Carbon\Carbon;
use App\Models\Activite;
use Illuminate\Support\Facades\Artisan;

class FactureControllerTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
        $facture = Facture::factory()->create(['etat' => 'brouillon']);

        $response = $this->get(route('admin.facture.valider', $facture->id));

        $response->assertRedirect(route('admin.facture.index', $facture->id));
        $this->assertEquals('verifier', Facture::find($facture->id)->etat);
    }

    public function test_envoyer_facture_sends_email_when_valid()
    {
        Mail::fake();

        $utilisateur = Utilisateur::factory()->create();
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $famille->utilisateurs()->attach($utilisateur->id);
        $facture = Facture::factory()->create([
            'etat' => 'verifier',
            'idFamille' => $famille->idFamille
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
            'etat' => 'brouillon',
            'idUtilisateur' => $utilisateur->id
        ]);

        $response = $this->get(route('admin.facture.envoyer', $facture->id));

        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertNothingSent();
    }

    public function test_redirects_to_index_on_invalid_facture()
    {
        $responseEnvoyerFacture = $this->get(route('admin.facture.envoyer', 1230));
        $responseValiderFacture = $this->get(route('admin.facture.valider', 1230));
        $responseExportFacture = $this->get(route('admin.facture.export', 1230));
        $responseShowFacture = $this->get(route('admin.facture.show', 1230));



        $responseEnvoyerFacture->assertRedirect(route('admin.facture.index'));
        $responseValiderFacture->assertRedirect(route('admin.facture.index'));
        $responseExportFacture->assertRedirect(route('admin.facture.index'));
        $responseShowFacture->assertRedirect(route('admin.facture.index'));
    }

  
    public function test_calculer_regularisation_negatif_ou_zero_sans_activites()
    {
        
        $famille = Famille::factory()->create();

        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(5)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(4)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(3)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(2)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(1)]);
        $controleur = new FactureController();
        $regularisation = $controleur->calculerRegularisation($famille->idFamille);
        $this->assertTrue( 0>=$regularisation);


    }

        public function test_calculer_regularisation_positif_quand_nombreux_activites()
    {
        $famille = Famille::create(['idFamille' => 999999, 'aineDansAutreSeaska' => false]);
        $enfant = Enfant::factory()->create(['nbFoisGarderie' => 0, 'idFamille' => $famille->idFamille, 'idEnfant' => 999999]);
        for ($i=0; $i < 30; $i++) {
            
            Etre::create(['idEnfant' => $enfant->idEnfant, 'activite' => 'garderie soir', 'dateP' => Carbon::now()->subMonths(2)->subDays($i)]);
            Activite::create(['activite' => 'garderie soir', 'dateP' => Carbon::now()->subMonths(2)->subDays($i)]);
        }

        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(5)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(4)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(3)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(2)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel'=> true ,  'dateC' => Carbon::now()->subMonths(1)]);
        $controleur = new FactureController();
        $regularisation = $controleur->calculerRegularisation($famille->idFamille);
        $this->assertTrue( 0<=$regularisation);


    }

    public function test_createFacture_cree_facture_pour_parent_avec_parite_100()
    {
        $famille = Famille::factory()->create();
        $parent1 = Utilisateur::factory()->create();
        $parent2 = Utilisateur::factory()->create();

        $famille->utilisateurs()->attach($parent1->idUtilisateur, ['parite' => 100]);
        $famille->utilisateurs()->attach($parent2->idUtilisateur, ['parite' => 0]);
        $famille->save();

        $controleur = new FactureController();
        $controleur->createFacture();
        $this->assertDatabaseHas('facture', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $parent1->idUtilisateur,
        ]);
        $this->assertDatabaseMissing('facture', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $parent2->idUtilisateur,
        ]);
    }

 
         public function test_createFacture_in_february_sets_previsionnel_false()
    {
        Carbon::setTestNow(Carbon::create(2024, 2, 1));
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $parent = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);
        $controleur = new FactureController();
        $controleur->createFacture();
        $this->assertDatabaseHas('facture', [
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
        ]);
        Carbon::setTestNow();
    }

        public function test_createFacture_in_august_sets_previsionnel_false()
    {
        Carbon::setTestNow(Carbon::create(2024, 8, 1));
        $famille = Famille::factory()->create();
        $parent = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);
        $controleur = new FactureController();
        $controleur->createFacture();
        $this->assertDatabaseHas('facture', [
            'idFamille' => $famille->idFamille,
            'previsionnel' => false,
        ]);
        Carbon::setTestNow();
    }
        
     public function test_monthly_schedule_triggers_create_facture()
    {
        // Simulate the 1st of a month so monthly events are due
        Carbon::setTestNow(Carbon::create(2024, 2, 1));

        $famille = Famille::factory()->create();
        $parent = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);

        $this->assertDatabaseMissing('facture', [
            'idFamille' => $famille->idFamille,
        ]);

        // Run the scheduler which should execute the registered monthly callback
        Artisan::call('schedule:run');

        // After running the scheduler, a facture should be created for the parent
        $this->assertDatabaseHas('facture', [
            'idFamille' => $famille->idFamille,
            'idUtilisateur' => $parent->idUtilisateur,
        ]);

        Carbon::setTestNow();
    }

}
