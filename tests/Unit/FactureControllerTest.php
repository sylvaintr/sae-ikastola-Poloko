<?php

namespace Tests\Unit;

use App\Models\Facture;
use App\Models\Famille;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class FactureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valider_facture_transitions_et_supprime_fichiers_manuels()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'manuel']);

        // create dummy manual files
        $base = 'factures/facture-' . $facture->idFacture;
        Storage::disk('public')->put($base . '.doc', 'x');
        Storage::disk('public')->put($base . '.odt', 'y');

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->validerFacture((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);

        $facture->refresh();
        $this->assertSame('manuel verifier', $facture->etat);

        // files deleted
        $this->assertFalse(Storage::disk('public')->exists($base . '.doc'));
        $this->assertFalse(Storage::disk('public')->exists($base . '.odt'));
    }

    public function test_exportFacture_retourne_binaire_manuel_si_present()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'manuel']);

        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn(['facture' => $facture]);

        $mockExporter = $this->getMockBuilder(\App\Services\FactureExporter::class)->onlyMethods(['serveManualFile'])->getMock();
        $mockExporter->method('serveManualFile')->willReturn('BINARYDATA');

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);
        $this->app->instance(\App\Services\FactureExporter::class, $mockExporter);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $result = $ctrl->exportFacture((string)$facture->idFacture, true);

        // then
        $this->assertSame('BINARYDATA', $result);
    }

    public function test_exportFacture_appelle_generate_pour_non_manuel()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);

        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn(['facture' => $facture]);

        $mockExporter = $this->getMockBuilder(\App\Services\FactureExporter::class)->onlyMethods(['generateAndServeFacture'])->getMock();
        $mockExporter->method('generateAndServeFacture')->willReturn('PDFBIN');

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);
        $this->app->instance(\App\Services\FactureExporter::class, $mockExporter);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $result = $ctrl->exportFacture((string)$facture->idFacture, true);

        // then
        $this->assertSame('PDFBIN', $result);
    }

    public function test_envoyerFacture_retourne_erreur_si_non_verifie()
    {
        // given
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create(['email' => 'to@example.test']);
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 1]);

        $facture = Facture::factory()->create(['etat' => 'brouillon', 'idFamille' => $famille->idFamille, 'idUtilisateur' => $user->idUtilisateur]);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->envoyerFacture((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_facturesData_retourne_json()
    {
        // given
        Facture::factory()->count(3)->create();

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->facturesData();

        // then
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $resp);
    }

    public function test_createFacture_cree_pour_familles_avec_parents()
    {
        // given
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 1]);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $ctrl->createFacture();

        // then
        $this->assertDatabaseHas((new Facture())->getTable(), ['idFamille' => $famille->idFamille]);
    }

    public function test_calculerRegularisation_retourne_difference_attendue()
    {
        // given
        $famille = Famille::factory()->create();
        $monthDate = \Carbon\Carbon::now()->subMonth()->startOfDay();

        $reg = Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => false, 'dateC' => $monthDate]);
        $prev = Facture::factory()->create(['idFamille' => $famille->idFamille, 'previsionnel' => true, 'dateC' => $monthDate]);

        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn([
            'totalPrevisionnel' => 5,
            'montantcotisation' => 10,
            'montantparticipation' => 0,
            'montantparticipationSeaska' => 0,
        ]);

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $res = $ctrl->calculerRegularisation($famille->idFamille);

        // then
        $this->assertSame(5, $res);
    }

    public function test_show_previsionnel_retourne_vue()
    {
        // given
        $famille = Famille::factory()->create();
        $facture = Facture::factory()->create(['etat' => 'brouillon', 'previsionnel' => false, 'idFamille' => $famille->idFamille]);

        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn([
            'facture' => $facture,
            'famille' => $famille,
            'nbEnfants' => 0,
            'montangarderie' => 0,
            'montantcotisation' => 0,
            'montantparticipation' => 0,
            'montantparticipationSeaska' => 0,
            'montanttotal' => 0,
            'totalPrevisionnel' => 0,
        ]);

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $view = $ctrl->show((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_show_manual_retourne_vue_avec_url_pdf()
    {
        // given
        Storage::fake('public');

        $famille = Famille::factory()->create();
        $facture = Facture::factory()->create(['etat' => 'manuel', 'idFamille' => $famille->idFamille]);

        $path = 'factures/facture-' . $facture->idFacture . '.pdf';
        Storage::disk('public')->put($path, '%PDF%');

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->show((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $resp);
        $this->assertStringContainsString('facture-' . $facture->idFacture, $resp->getData()['fichierpdf']);
    }

    public function test_index_retourne_vue()
    {
        // given
        // none

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $view = $ctrl->index();

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_exportFacture_retourne_redirect_calculateur_quand_calculateur_retourne_redirect()
    {
        // given
        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $redirect = redirect()->route('admin.facture.index');
        $mockCalculator->method('calculerMontantFacture')->willReturn($redirect);

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $res = $ctrl->exportFacture('1');

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
    }

    public function test_show_manual_missing_pdf_redirige()
    {
        // given
        Storage::fake('public');
        $facture = Facture::factory()->create(['etat' => 'manuel']);

        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn(['facture' => $facture]);
        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $res = $ctrl->show((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
    }

    public function test_calculerRegularisation_retourne_zero_si_aucune_facture()
    {
        // given
        $famille = Famille::factory()->create();
        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $res = $ctrl->calculerRegularisation($famille->idFamille);

        // then
        $this->assertSame(0, $res);
    }

    public function test_validerFacture_retourne_erreur_si_deja_validee()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'verifier']);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->validerFacture((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_update_avec_fichier_upload_invalide_retourne_erreur()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);

        $file = UploadedFile::fake()->create('facture.doc', 10, 'application/msword');

        $request = \Illuminate\Http\Request::create('/', 'POST', [], [], ['facture' => $file]);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->update($request, (string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_envoyerFacture_retourne_erreur_si_manquante()
    {
        // given
        // none

        // when

        // then
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->envoyerFacture('999999');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_validerFacture_retourne_erreur_si_manquante()
    {
        // given
        // none

        // when

        // then
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->validerFacture('999999');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_envoyerFacture_envoie_mail_si_verifie()
    {
        // given
        \Illuminate\Support\Facades\Mail::fake();

        $famille = \App\Models\Famille::factory()->create();
        $user = \App\Models\Utilisateur::factory()->create(['email' => 'to@example.test']);
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 1]);

        $facture = \App\Models\Facture::factory()->create(['etat' => 'verifier', 'idFamille' => $famille->idFamille, 'idUtilisateur' => $user->idUtilisateur]);

        $mockCalculator = $this->getMockBuilder(\App\Services\FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $mockCalculator->method('calculerMontantFacture')->willReturn(['facture' => $facture, 'famille' => $famille, 'enfants' => []]);

        $mockExporter = $this->getMockBuilder(\App\Services\FactureExporter::class)->onlyMethods(['generateAndServeFacture'])->getMock();
        $mockExporter->method('generateAndServeFacture')->willReturn('%PDF%');

        $this->app->instance(\App\Services\FactureCalculator::class, $mockCalculator);
        $this->app->instance(\App\Services\FactureExporter::class, $mockExporter);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->envoyerFacture((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_update_definit_etat_manuel_meme_sans_fichier()
    {
        // given
        $facture = \App\Models\Facture::factory()->create(['etat' => 'brouillon']);
        // use a real request without uploaded file
        $request = \Illuminate\Http\Request::create('/', 'POST', []);

        // when
        $ctrl = new \App\Http\Controllers\FactureController();
        $resp = $ctrl->update($request, (string)$facture->idFacture);

        // then: ensure controller returned a redirect and state updated
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $facture->refresh();
        $this->assertSame('manuel', $facture->etat);
    }
}
