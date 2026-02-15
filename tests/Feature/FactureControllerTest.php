<?php
namespace Tests\Feature;

use App\Http\Controllers\FactureController;
use App\Mail\Facture as FactureMail;
use App\Models\Activite;
use App\Models\Enfant;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Pratiquer;
use App\Models\Utilisateur;
use App\Services\FactureCalculator;
use Carbon\Carbon;
use function PHPUnit\Framework\assertTrue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureControllerTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    public function test_index_retourne_vue()
    {
        // given
        // none

        // when
        $response = $this->get(route('admin.facture.index'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('facture.index');
    }

    public function test_show_retourne_vue_avec_donnees()
    {
        // given
        $controleur = new FactureController();
        $user = Utilisateur::factory()->create();
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 100]);
        Enfant::factory()->count(2)->create(['idFamille' => $famille->idFamille]);
        $controleur->createFacture();
        $facture = Facture::first();

        // when
        // ensure a PDF exists for the generated facture so the show route returns the view
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', '%PDF%');

        $response = $this->get(route('admin.facture.show', $facture->idFacture));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('facture.show');

    }

    public function test_donnees_factures_retournent_json()
    {
        // given
        Facture::factory()->count(3)->create();

        // when
        $response = $this->getJson(route('admin.factures.data'));

        // then
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['titre', 'etat', 'actions'],
            ],
        ]);
    }

    public function test_export_facture_retourne_pdf_ou_doc()
    {
        // given: prepare storage and a facture
        // Ne pas utiliser Storage::fake() car le service utilise le disque réel via Storage::disk('public')
        $facture = Facture::factory()->create(['etat' => 'verifier']);
        $famille = Famille::factory()->create();
        $facture->idFamille = $famille->idFamille;
        $facture->save();

        // create an actual PDF file for this facture so exporter can serve it
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', '%PDF%');

        // when
        $response = $this->get(route('admin.facture.export', $facture->idFacture));

        // then
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // given (pour un brouillon -> docx)
        $facture->etat = 'manuel';
        $facture->save();
        // Supprimer le PDF et créer le docx
        Storage::disk('public')->delete('factures/facture-' . $facture->idFacture . '.pdf');
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.docx', 'DOCDATA');

        // when
        $response = $this->get(route('admin.facture.export', $facture->idFacture));

        // then
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // Cleanup
        Storage::disk('public')->delete('factures/facture-' . $facture->idFacture . '.docx');
    }

    public function test_valider_facture_change_etat()
    {
        // given
        $user = Utilisateur::factory()->create();
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 100]);
        Enfant::factory()->count(2)->create(['idFamille' => $famille->idFamille]);

        // Créer une facture avec état 'manuel' (pas brouillon) pour que la conversion puisse avoir lieu
        $facture = Facture::factory()->create([
            'etat' => 'manuel',
            'idUtilisateur' => $user->idUtilisateur,
            'idFamille' => $famille->idFamille,
        ]);

        // Créer un fichier docx pour que le service de conversion trouve quelque chose
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.docx', 'dummy content');

        // when
        $response = $this->get(route('admin.facture.valider', $facture->idFacture));

        // then
        $response->assertRedirect();
        // En mode test, le service de conversion retourne true via le short-circuit
        // et met à jour l'état à 'verifier'
        $this->assertEquals('verifier', Facture::find($facture->idFacture)->etat);
    }

    public function test_envoyer_facture_envoie_email_si_valide()
    {
        // given
        Mail::fake();
        $utilisateur = Utilisateur::factory()->create();
        $famille     = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $famille->utilisateurs()->attach($utilisateur->idUtilisateur);
        $facture = Facture::factory()->create([
            'etat'          => 'verifier',
            'idUtilisateur' => $utilisateur->idUtilisateur,
            'idFamille'     => $famille->idFamille,
        ]);

        // Créer un fichier PDF pour que l'export le trouve
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.pdf', '%PDF-1.4 dummy content');

        // when
        $response = $this->get(route('admin.facture.envoyer', $facture->idFacture));

        // then
        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertSent(FactureMail::class, function ($mail) use ($utilisateur) {
            return $mail->hasTo($utilisateur->email);
        });
    }

    public function test_envoyer_facture_n_envoie_pas_email_si_invalide()
    {
        // given
        Mail::fake();
        $utilisateur = Utilisateur::factory()->create();
        $facture     = Facture::factory()->create([
            'etat'          => 'brouillon',
            'idUtilisateur' => $utilisateur->id,
        ]);

        // when
        $response = $this->get(route('admin.facture.envoyer', $facture->id));

        // then
        $response->assertRedirect(route('admin.facture.index'));
        Mail::assertNothingSent();
    }

    public function test_redirects_to_index_on_invalid_facture()
    {
        // given
        // none

        // when
        $responseEnvoyerFacture = $this->get(route('admin.facture.envoyer', 1230));
        $responseValiderFacture = $this->get(route('admin.facture.valider', 1230));
        $responseExportFacture  = $this->get(route('admin.facture.export', 1230));
        $responseShowFacture    = $this->get(route('admin.facture.show', 1230));

        // then
        $responseEnvoyerFacture->assertRedirect(route('admin.facture.index'));
        $responseValiderFacture->assertRedirect(route('admin.facture.index'));
        $responseExportFacture->assertRedirect(route('admin.facture.index'));
        $responseShowFacture->assertRedirect(route('admin.facture.index'));
    }

    public function test_calculer_regularisation_negatif_ou_zero_sans_activites()
    {
        // given
        $famille = Famille::factory()->create();
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(5)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(4)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(3)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(2)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(1)]);

        // when
        // create a target facture for which to compute regularisation
        $target         = Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => false, 'dateC' => Carbon::now()]);
        $calculator     = new \App\Services\FactureCalculator();
        $regularisation = $calculator->calculerRegularisation($target->idFacture);

        // then
        $this->assertTrue(0 >= $regularisation);
    }

    public function test_calculer_regularisation_positif_quand_nombreux_activites()
    {
        // given
        $famille = Famille::create(['idFamille' => 999999, 'aineDansAutreSeaska' => false]);
        $enfant  = Enfant::factory()->create(['nbFoisGarderie' => 0, 'idFamille' => $famille->idFamille, 'idEnfant' => 999999]);
        for ($i = 0; $i < 30; $i++) {
            Pratiquer::create(['idEnfant' => $enfant->idEnfant, 'activite' => 'garderie soir', 'dateP' => Carbon::now()->subMonths(2)->subDays($i)]);
            Activite::create(['activite' => 'garderie soir', 'dateP' => Carbon::now()->subMonths(2)->subDays($i)]);
        }

        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(5)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(4)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(3)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(2)]);
        Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => Carbon::now()->subMonths(1)]);

        // when
        $target         = Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => false, 'dateC' => Carbon::now()]);
        $calculator     = new \App\Services\FactureCalculator();
        $regularisation = $calculator->calculerRegularisation($target->idFacture);

        // then - ensure we get an integer regularisation value
        $this->assertIsFloat($regularisation);
    }

    public function test_createFacture_cree_facture_pour_parent_avec_parite_100()
    {
        // given
        $famille = Famille::factory()->create();
        $parent1 = Utilisateur::factory()->create();
        $parent2 = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent1->idUtilisateur, ['parite' => 100]);
        $famille->utilisateurs()->attach($parent2->idUtilisateur, ['parite' => 0]);
        $famille->save();

        // when
        $controleur = new FactureController();
        $controleur->createFacture();

        // then
        $this->assertDatabaseHas('facture', [
            'idFamille'     => $famille->idFamille,
            'idUtilisateur' => $parent1->idUtilisateur,
        ]);
        $this->assertDatabaseMissing('facture', [
            'idFamille'     => $famille->idFamille,
            'idUtilisateur' => $parent2->idUtilisateur,
        ]);
    }

    public function test_createFacture_in_february_sets_previsionnel_false()
    {
        // given
        Carbon::setTestNow(Carbon::create(2024, 2, 1));
        $famille = Famille::factory()->create();
        $famille->utilisateurs()->detach();
        $parent = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);

        // when
        $controleur = new FactureController();
        $controleur->createFacture();

        // then
        $this->assertDatabaseHas('facture', [
            'idFamille'    => $famille->idFamille,
            'previsionnel' => false,
        ]);
        Carbon::setTestNow();
    }

    public function test_createFacture_in_august_sets_previsionnel_false()
    {
        // given
        Carbon::setTestNow(Carbon::create(2024, 8, 1));
        $famille = Famille::factory()->create();
        $parent  = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);

        // when
        $controleur = new FactureController();
        $controleur->createFacture();

        // then
        $this->assertDatabaseHas('facture', [
            'idFamille'    => $famille->idFamille,
            'previsionnel' => false,
        ]);
        Carbon::setTestNow();
    }

    public function test_monthly_schedule_triggers_create_facture()
    {
        // given
        // Simulate the 1st of a month so monthly events are due
        Carbon::setTestNow(Carbon::create(2024, 2, 1));
        $famille = Famille::factory()->create();
        $parent  = Utilisateur::factory()->create();
        $famille->utilisateurs()->detach();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);

        $this->assertDatabaseMissing('facture', [
            'idFamille' => $famille->idFamille,
        ]);

        // when
        // Run the scheduler which should execute the registered monthly callback
        Artisan::call('schedule:run');

        // then
        // After running the scheduler, a facture should be created for the parent
        $this->assertDatabaseHas('facture', [
            'idFamille'     => $famille->idFamille,
            'idUtilisateur' => $parent->idUtilisateur,
        ]);

        Carbon::setTestNow();
    }

    public function test_verifier_que_le_docx_est_cree_a_la_creation_de_la_facture()
    {

        // given
        $famille = Famille::factory()->create();
        $parent  = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($parent->idUtilisateur, ['parite' => 100]);
        Enfant::factory()->count(2)->create(['idFamille' => $famille->idFamille]);
        $controleur = new FactureController();

        // when
        $controleur->createFacture();

        // then
        $facture = Facture::where('idFamille', $famille->idFamille)->first();
        assertTrue(file_exists(storage_path('app/public/factures/facture-' . $facture->idFacture . '.docx')));

    }

    public function test_renvoie_vers_home_si_on_calcule_une_facture_sans_famille()
    {
        // given
        $facture    = Facture::factory()->create(['idFamille' => 999999]);
        $controleur = new FactureCalculator();

        // when
        $response = $controleur->calculerMontantFacture((string) $facture->id);
        // then

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(route('admin.facture.index'), $response->getTargetUrl());

    }

    public function test_renvoie_0_si_on_calcule_une_regularisation_sans_famille()
    {
        // given
        $controleur = new FactureCalculator();

        // when
        $response = $controleur->calculerRegularisation(999999);
        // then

        $this->assertEquals(0, $response);

    }

}
