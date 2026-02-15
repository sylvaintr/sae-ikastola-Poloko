<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Facture;
use App\Models\Utilisateur;
use App\Services\FactureConversionService;

class FactureControllerValiderFactureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_validerFacture_converts_and_updates_state_when_conversion_succeeds()
    {
        // given
        $user = Utilisateur::factory()->create();
        $facture = Facture::factory()->create([
            'etat' => 'manuel',
            'idUtilisateur' => $user->idUtilisateur,
        ]);

        // Créer un fichier pour que le service de conversion le trouve
        \Illuminate\Support\Facades\Storage::disk('public')->put(
            'factures/facture-' . $facture->idFacture . '.docx',
            'dummy content'
        );

        // when - En mode test, le service retourne true via le short-circuit
        $response = $this->get(route('admin.facture.valider', $facture->idFacture));

        // then
        $response->assertRedirect(route('admin.facture.index'));
        $this->assertEquals('verifier', Facture::find($facture->idFacture)->etat);

        // Cleanup
        \Illuminate\Support\Facades\Storage::disk('public')->delete(
            'factures/facture-' . $facture->idFacture . '.docx'
        );
    }
}
