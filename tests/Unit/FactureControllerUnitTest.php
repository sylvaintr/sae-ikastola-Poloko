<?php

namespace Tests\Unit;

use App\Http\Controllers\FactureController;
use App\Models\Facture;
use App\Models\Famille;
use App\Models\Utilisateur;
use App\Mail\Facture as FactureMail;
use App\Services\FactureExporter;
use App\Services\FactureCalculator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactureControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_facture_delegue_a_exporteur_et_retourne_reponse()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'verifier']);

        $montants = ['facture' => $facture, 'famille' => null, 'enfants' => []];

        $calculator = $this->getMockBuilder(FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $calculator->method('calculerMontantFacture')->willReturn($montants);
        app()->instance(FactureCalculator::class, $calculator);

        $exporter = $this->getMockBuilder(FactureExporter::class)->onlyMethods(['generateAndServeFacture'])->getMock();
        $exporter->method('generateAndServeFacture')->willReturn(response('OK', 200)->header('Content-Type', 'application/pdf'));
        app()->instance(FactureExporter::class, $exporter);

        // when
        $ctrl = new FactureController();
        $resp = $ctrl->exportFacture((string)$facture->idFacture, false);

        // then
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $resp);
        $this->assertEquals('application/pdf', $resp->headers->get('Content-Type'));
    }

    public function test_envoyer_facture_envoie_mail_si_verifie()
    {
        // given
        Mail::fake();
        Storage::fake('public');

        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create(['email' => 'client@test']);
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 1]);

        $facture = Facture::factory()->create(['etat' => 'verifier', 'idFamille' => $famille->idFamille]);

        $montants = ['facture' => $facture, 'famille' => $famille, 'enfants' => []];

        $calculator = $this->getMockBuilder(FactureCalculator::class)->onlyMethods(['calculerMontantFacture'])->getMock();
        $calculator->method('calculerMontantFacture')->willReturn($montants);
        app()->instance(FactureCalculator::class, $calculator);

        $exporter = $this->getMockBuilder(FactureExporter::class)->onlyMethods(['generateAndServeFacture'])->getMock();
        $exporter->method('generateAndServeFacture')->willReturn('%PDF%');
        app()->instance(FactureExporter::class, $exporter);

        // when
        $ctrl = new FactureController();
        $resp = $ctrl->envoyerFacture((string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        Mail::assertSent(FactureMail::class);
    }

    public function test_mise_a_jour_sans_fichier_definit_facture_manuel()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);
        $request = \Illuminate\Http\Request::create('/admin/facture', 'POST', []);

        // when
        $ctrl = new FactureController();
        $resp = $ctrl->update($request, (string)$facture->idFacture);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $facture->refresh();
        $this->assertSame('manuel', $facture->etat);
    }
}
