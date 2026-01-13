<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;
use App\Models\Facture;
use App\Models\Tache;
use App\Models\DemandeHistorique;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_famille_factures_relationship_returns_linked_factures()
    {
        $famille = Famille::factory()->create();

        Facture::factory()->count(2)->create(['idFamille' => $famille->idFamille]);

        $famille->load('factures');

        $this->assertCount(2, $famille->factures);
        $this->assertInstanceOf(Facture::class, $famille->factures->first());
        $this->assertEquals($famille->idFamille, $famille->factures->first()->idFamille);
    }

    public function test_demandehistorique_demande_belongs_to_tache()
    {
        $tache = Tache::factory()->create();

        $historique = DemandeHistorique::create([
            'idDemande' => $tache->idTache,
            'statut' => 's',
            'titre' => 't',
            'responsable' => 'r',
            'depense' => 0,
            'date_evenement' => now(),
            'description' => 'd',
        ]);

        $this->assertInstanceOf(Tache::class, $historique->demande);
        $this->assertEquals($tache->idTache, $historique->demande->idTache);
    }
}
