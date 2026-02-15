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

        // Mock the conversion service to be called and return true
        $mock = $this->createMock(FactureConversionService::class);
        $mock->expects($this->once())
            ->method('convertFactureToPdf')
            ->with($this->isInstanceOf(Facture::class))
            ->willReturn(true);

        $this->app->instance(FactureConversionService::class, $mock);

        // when
        $response = $this->get(route('admin.facture.valider', $facture->idFacture));

        // then
        $response->assertRedirect(route('admin.facture.index'));
        $this->assertEquals('verifier', Facture::find($facture->idFacture)->etat);
    }
}
