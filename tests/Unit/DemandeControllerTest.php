<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\DemandeController;
use App\Models\Tache;
use App\Models\Document;
use App\Models\DemandeHistorique;

class DemandeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_retourne_vue_et_valeurs_par_defaut()
    {
        Tache::factory()->count(3)->create();

        $request = Request::create('/demandes', 'GET', ['search' => 'foo', 'etat' => 'all']);
        $controller = new DemandeController();
        $view = $controller->index($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();

        $this->assertArrayHasKey('demandes', $data);
        $this->assertArrayHasKey('etats', $data);
        $this->assertArrayHasKey('urgences', $data);
    }

    public function test_create_retourne_vue()
    {
        $controller = new DemandeController();
        $view = $controller->create();

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();
        $this->assertArrayHasKey('types', $data);
        $this->assertArrayHasKey('urgences', $data);
    }

    public function test_show_retourne_vue_avec_photos_et_historique()
    {
        Storage::fake('public');

        $tache = Tache::factory()->create();

        $path = 'demandes/' . Str::random(8) . '.jpg';
        Storage::disk('public')->put($path, 'data');
        Document::create(['idDocument' => null, 'idTache' => $tache->idTache, 'nom' => basename($path), 'chemin' => $path, 'type' => 'jpg', 'etat' => 'actif']);

        DemandeHistorique::create(['idHistorique' => null, 'idDemande' => $tache->idTache, 'statut' => 's1', 'depense' => 12.5, 'titre' => 'h1']);
        DemandeHistorique::create(['idHistorique' => null, 'idDemande' => $tache->idTache, 'statut' => 's2', 'depense' => 7.5, 'titre' => 'h2']);

        $controller = new DemandeController();
        $view = $controller->show($tache);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();

        $this->assertArrayHasKey('photos', $data);
        $this->assertCount(1, $data['photos']);
        $this->assertArrayHasKey('historiques', $data);
        $this->assertEquals(20.0, $data['totalDepense']);
    }

    public function test_store_cree_tache_et_historique_sans_photos()
    {
        $payload = [
            'titre' => 'New demande',
            'description' => 'desc',
            'type' => 'other',
            'urgence' => 'faible',
            'etat' => 'En attente',
        ];

        $request = new class($payload) extends Request {
            public function __construct($data = [])
            {
                parent::__construct();
                $this->replace($data);
            }

            public function validate(array $rules)
            {
                return $this->all();
            }
        };

        $controller = new DemandeController();
        $response = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $this->assertDatabaseHas('tache', ['titre' => 'New demande']);
        $tache = Tache::where('titre', 'New demande')->first();
        $this->assertNotNull($tache);
        $this->assertDatabaseHas('demande_historique', ['idDemande' => $tache->idTache]);
    }

    public function test_edit_redirige_quand_termine()
    {
        $tache = Tache::factory()->create(['etat' => 'Terminé']);
        $controller = new DemandeController();
        $response = $controller->edit($tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_update_applique_mises_a_jour()
    {
        $tache = Tache::factory()->create(['titre' => 'old']);

        $payload = ['titre' => 'updated', 'description' => 'newdesc', 'urgence' => 'elevee', 'etat' => 'En cours'];
        $request = new class($payload) extends Request {
            public function __construct($data = [])
            {
                parent::__construct();
                $this->replace($data);
            }

            public function validate(array $rules)
            {
                return $this->all();
            }
        };

        $controller = new DemandeController();
        $response = $controller->update($request, $tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('tache', ['idTache' => $tache->idTache, 'titre' => 'updated']);
    }

    public function test_storeHistorique_cree_historique()
    {
        $tache = Tache::factory()->create(['etat' => 'En cours']);

        $payload = ['titre' => 'hist', 'description' => 'd', 'depense' => 5.0];
        $request = new class($payload) extends Request {
            public function __construct($data = [])
            {
                parent::__construct();
                $this->replace($data);
            }

            public function validate(array $rules)
            {
                return $this->all();
            }
        };

        $controller = new DemandeController();
        $response = $controller->storeHistorique($request, $tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('demande_historique', ['idDemande' => $tache->idTache, 'titre' => $tache->titre]);
    }

    public function test_validate_demande_definit_termine_et_cree_historique()
    {
        $tache = Tache::factory()->create(['etat' => 'En cours']);

        $controller = new DemandeController();
        $response = $controller->validateDemande($tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('tache', ['idTache' => $tache->idTache, 'etat' => 'Terminé']);
        $this->assertDatabaseHas('demande_historique', ['idDemande' => $tache->idTache]);
    }

    public function test_destroy_supprime_fichiers_et_enregistrements()
    {
        Storage::fake('public');

        $tache = Tache::factory()->create();
        $path = 'demandes/' . Str::random(8) . '.jpg';
        Storage::disk('public')->put($path, 'content');

        $doc = Document::create(['idDocument' => null, 'idTache' => $tache->idTache, 'nom' => basename($path), 'chemin' => $path, 'type' => 'jpg', 'etat' => 'actif']);
        DemandeHistorique::create(['idHistorique' => null, 'idDemande' => $tache->idTache, 'statut' => 's', 'depense' => 1.0, 'titre' => 'h']);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $controller = new DemandeController();
        $response = $controller->destroy($tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseMissing('tache', ['idTache' => $tache->idTache]);
        $this->assertDatabaseMissing('document', ['idDocument' => $doc->idDocument]);
        $this->assertDatabaseMissing('demande_historique', ['idDemande' => $tache->idTache]);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }
}
