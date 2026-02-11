<?php
namespace Tests\Unit;

use App\Http\Controllers\DemandeController;
use App\Models\DemandeHistorique;
use App\Models\Document;
use App\Models\Tache;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemandeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_retourne_vue_et_les_valeurs_par_defaut()
    {
        // given
        // none

        // when

        // then
        Tache::factory()->count(3)->create();

        $request    = Request::create('/demandes', 'GET', ['search' => 'foo', 'etat' => 'all']);
        $controller = new DemandeController();
        $view       = $controller->index($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();

        $this->assertArrayHasKey('demandes', $data);
        $this->assertArrayHasKey('etats', $data);
        $this->assertArrayHasKey('urgences', $data);
    }

    public function test_create_retourne_une_vue()
    {
        // given
        // none

        // when

        // then
        $controller = new DemandeController();
        $view       = $controller->create();

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();
        $this->assertArrayHasKey('types', $data);
        $this->assertArrayHasKey('urgences', $data);
    }

    public function test_show_retourne_une_vue_avec_photos_et_historique()
    {
        // given
        // none

        // when

        // then
        Storage::fake('public');

        $tache = Tache::factory()->create();

        $path = 'demandes/' . Str::random(8) . '.jpg';
        Storage::disk('public')->put($path, 'data');
        Document::create(['idDocument' => null, 'idTache' => $tache->idTache, 'nom' => basename($path), 'chemin' => $path, 'type' => 'jpg', 'etat' => 'actif']);

        DemandeHistorique::create(['idHistorique' => null, 'idDemande' => $tache->idTache, 'statut' => 's1', 'depense' => 12.5, 'titre' => 'h1']);
        DemandeHistorique::create(['idHistorique' => null, 'idDemande' => $tache->idTache, 'statut' => 's2', 'depense' => 7.5, 'titre' => 'h2']);

        $controller = new DemandeController();
        $view       = $controller->show($tache);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();

        $this->assertArrayHasKey('photos', $data);
        $this->assertCount(1, $data['photos']);
        $this->assertArrayHasKey('historiques', $data);
        $this->assertEquals(20.0, $data['totalDepense']);
    }

    public function test_store_cree_une_tache_et_un_historique_sans_photos()
    {
        // given
        // none

        // when

        // then
        $payload = [
            'titre'       => 'New demande',
            'description' => 'desc',
            'type'        => 'other',
            'urgence'     => 'faible',
            'etat'        => 'En attente',
        ];

        $request = new class($payload) extends Request
        {
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
        $response   = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $this->assertDatabaseHas('tache', ['titre' => 'New demande']);
        $tache = Tache::where('titre', 'New demande')->first();
        $this->assertNotNull($tache);
        $this->assertDatabaseHas('demande_historique', ['idDemande' => $tache->idTache]);
    }

    public function test_edit_redirige_lorsque_termine()
    {
        // given
        // none

        // when

        // then
        $tache      = Tache::factory()->create(['etat' => 'Terminé']);
        $controller = new DemandeController();
        $response   = $controller->edit($tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_update_applique_les_mises_a_jour()
    {
        // given
        // none

        // when

        // then
        $tache = Tache::factory()->create(['titre' => 'old']);

        $payload = ['titre' => 'updated', 'description' => 'newdesc', 'urgence' => 'elevee', 'etat' => 'En cours'];
        $request = new class($payload) extends Request
        {
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
        $response   = $controller->update($request, $tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('tache', ['idTache' => $tache->idTache, 'titre' => 'updated']);
    }

    public function test_storeHistorique_cree_un_historique()
    {
        // given
        // none

        // when

        // then
        $tache = Tache::factory()->create(['etat' => 'En cours']);

        $payload = ['titre' => 'hist', 'description' => 'd', 'depense' => 5.0];
        $request = new class($payload) extends Request
        {
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
        $response   = $controller->storeHistorique($request, $tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('demande_historique', ['idDemande' => $tache->idTache, 'titre' => $tache->titre]);
    }

    public function test_validate_demande_definit_termine_et_cree_un_historique()
    {
        // given
        // none

        // when

        // then
        $tache = Tache::factory()->create(['etat' => 'En cours']);

        $controller = new DemandeController();
        $response   = $controller->validateDemande($tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('tache', ['idTache' => $tache->idTache, 'etat' => 'Terminé']);
        $this->assertDatabaseHas('demande_historique', ['idDemande' => $tache->idTache]);
    }

    public function test_destroy_supprime_les_fichiers_et_les_enregistrements()
    {
        // given
        // none

        // when

        // then
        Storage::fake('public');

        $tache = Tache::factory()->create();
        $path  = 'demandes/' . Str::random(8) . '.jpg';
        Storage::disk('public')->put($path, 'content');

        $doc = Document::create(['idDocument' => null, 'idTache' => $tache->idTache, 'nom' => basename($path), 'chemin' => $path, 'type' => 'jpg', 'etat' => 'actif']);
        DemandeHistorique::create(['idHistorique' => null, 'idDemande' => $tache->idTache, 'statut' => 's', 'depense' => 1.0, 'titre' => 'h']);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $controller = new DemandeController();
        $response   = $controller->destroy($tache);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseMissing('tache', ['idTache' => $tache->idTache]);
        $this->assertDatabaseMissing('document', ['idDocument' => $doc->idDocument]);
        $this->assertDatabaseMissing('demande_historique', ['idDemande' => $tache->idTache]);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Création d'un utilisateur pour l'authentification
        $this->admin = Utilisateur::factory()->create();
        // Ensure CA role exists and assign to admin so route middleware 'can:access-demande' passes
        $role = \App\Models\Role::firstOrCreate(['name' => 'CA'], ['guard_name' => 'web']);
        $this->admin->roles()->attach($role->idRole);
    }

    /**
     * Test de buildDemandesQuery : Recherche par ID numérique exact.
     * Cas : if (ctype_digit($searchTerm)) { ... }
     */
    public function test_index_filters_by_exact_numeric_id()
    {
        // Création de 3 tâches pour le test
        $tache1 = Tache::factory()->create(['idTache' => 10, 'titre' => 'Tâche 10']);
        $tache2 = Tache::factory()->create(['idTache' => 100, 'titre' => 'Tâche 100']); // Contient "10" mais n'est pas 10
        $tache3 = Tache::factory()->create(['idTache' => 11, 'titre' => 'Tâche 11']);

        // Recherche de "10"
        // Appel direct du contrôleur pour bypasser le middleware de route
        $controller = new DemandeController();
        $view       = $controller->index(Request::create('/demandes', 'GET', ['search' => '10']));
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();
        $this->assertArrayHasKey('demandes', $data);
        $ids = collect($data['demandes']->items())->pluck('idTache')->all();
        $this->assertContains(10, $ids);
        $this->assertNotContains(100, $ids);
        $this->assertNotContains(11, $ids);
    }

    /**
     * Test de buildDemandesQuery : Recherche texte standard (fallback).
     */
    public function test_index_filters_by_text_when_search_is_not_numeric()
    {
        $tacheAlpha = Tache::factory()->create(['titre' => 'Réparation Alpha']);
        $tacheBeta  = Tache::factory()->create(['titre' => 'Ménage Beta']);

        $controller = new DemandeController();
        $view       = $controller->index(Request::create('/demandes', 'GET', ['search' => 'Alpha']));
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();
        $ids  = collect($data['demandes']->items())->pluck('titre')->all();
        $this->assertContains('Réparation Alpha', $ids);
        $this->assertNotContains('Ménage Beta', $ids);
    }

    /**
     * Test de exportAllCsv.
     */
    public function test_export_all_csv_generates_stream_with_correct_data()
    {
        // Création de données pour l'export
        $tache = Tache::factory()->create([
            'idTache'  => 999,
            'titre'    => 'Exportable Task',
            'montantP' => 500.00,
        ]);

        // Appel de la route d'export (nécessite d'être authentifié)
        // Assurez-vous que votre route s'appelle bien 'demandes.export_all' dans web.php
        // Appel direct du contrôleur pour bypasser le middleware
        $controller = new DemandeController();
        $response   = $controller->exportAllCsv(Request::create('/demande/export-all-csv', 'GET'));

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith(chr(0xEF) . chr(0xBB) . chr(0xBF), $content);
        $this->assertStringContainsString('Exportable Task', $content);
        $this->assertStringContainsString('999', $content);
        $this->assertStringContainsString('500,00', $content);
    }

    /**
     * Test de showDocument : Succès.
     */
    public function test_show_document_returns_file_if_exists_and_linked()
    {
        Storage::fake('public');

        $tache = Tache::factory()->create();

        // Création du fichier physique
        $filename = 'documents/test.jpg';
        Storage::disk('public')->put($filename, 'fake image content');

        // Création de l'entrée en base
        $document = Document::factory()->create([
            'idTache' => $tache->idTache,
            'chemin'  => $filename,
            'nom'     => 'test.jpg',
        ]);

        $controller = new DemandeController();
        $resp       = $controller->showDocument($tache, $document);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $resp);
        $this->assertNotEmpty($resp->headers->get('Content-Type'));
    }

    /**
     * Test de showDocument : Échec si le document n'appartient pas à la tâche.
     */
    public function test_show_document_aborts_if_document_does_not_belong_to_task()
    {
        $tache1 = Tache::factory()->create();
        $tache2 = Tache::factory()->create(); // Autre tâche

        $document = Document::factory()->create([
            'idTache' => $tache2->idTache, // Lié à tâche 2
            'chemin'  => 'dummy.jpg',
        ]);

        // On essaie d'accéder au doc de tâche 2 via l'URL de tâche 1
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller = new DemandeController();
        $controller->showDocument($tache1, $document);
    }

    /**
     * Test de showDocument : Échec si le fichier physique est absent.
     */
    public function test_show_document_aborts_if_file_missing_on_disk()
    {
        Storage::fake('public');

        $tache = Tache::factory()->create();

        $document = Document::factory()->create([
            'idTache' => $tache->idTache,
            'chemin'  => 'documents/ghost.jpg', // N'existe pas sur le disque fake
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller = new DemandeController();
        $controller->showDocument($tache, $document);
    }
}
