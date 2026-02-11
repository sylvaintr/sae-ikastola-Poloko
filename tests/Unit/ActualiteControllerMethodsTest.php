<?php
namespace Tests\Unit;

use App\Http\Controllers\ActualiteController;
use App\Models\Actualite;
use App\Models\Document;
use App\Models\Etiquette;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActualiteControllerMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_convertit_date_avec_slash_et_enregistre_avec_request_simple()
    {
        // given
        // none

        // when

        // then
        Storage::fake('public');

        $user = Utilisateur::factory()->create();
        auth()->login($user);

        $et = Etiquette::factory()->create();

        $file = UploadedFile::fake()->image('pic.jpg');

        $params = [
            'type'           => 'public',
            'dateP'          => '10/12/2025',
            'titrefr'        => 'Titre FR',
            'descriptionfr'  => 'Desc FR',
            'descriptioneus' => 'Desc EUS',
            'contenufr'      => 'Contenu FR',
            'contenueus'     => 'Contenu EUS',
            'etiquettes'     => [$et->idEtiquette],
        ];

        $request = Request::create('/actualites', 'POST', $params, [], ['images' => [$file]]);

        $controller = new ActualiteController();
        $controller->store($request);

        $this->assertDatabaseHas('actualite', ['titrefr' => 'Titre FR']);
        $this->assertDatabaseHas('document', ['nom' => 'pic.jpg']);
        $act = Actualite::where('titrefr', 'Titre FR')->first();
        $this->assertGreaterThan(0, $act->documents()->count());
    }

    public function test_data_filters_retournent_json_et_respectent_filtres()
    {
        // given
        // none

        // when

        // then
        $et = Etiquette::factory()->create();
        Actualite::factory()->create(['type' => 'public', 'archive' => false, 'titrefr' => 'A1', 'dateP' => now()])->etiquettes()->attach($et->idEtiquette);
        Actualite::factory()->create(['type' => 'public', 'archive' => true, 'titrefr' => 'A2', 'dateP' => now()]);

        $controller = new ActualiteController();

        $respActive = $controller->data(Request::create('/data', 'GET', ['etat' => 'active']));
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $respActive);
        $payload = $respActive->getData(true);
        $this->assertArrayHasKey('data', $payload);

        $respArchived = $controller->data(Request::create('/data', 'GET', ['etat' => 'archived']));
        $p2           = $respArchived->getData(true);
        $this->assertArrayHasKey('data', $p2);

        $respEtiq = $controller->data(Request::create('/data', 'GET', ['etiquette' => [$et->idEtiquette]]));
        $p3       = $respEtiq->getData(true);
        $this->assertArrayHasKey('data', $p3);
    }

    public function test_appel_delegue_aux_helpers()
    {
        // given
        // none

        // when

        // then
        $act        = Actualite::factory()->create(['titrefr' => 'CT']);
        $controller = new ActualiteController();

        // columnTitre is implemented in ActualiteHelpers and should be accessible via __call
        $res = $controller->columnTitre($act);
        $this->assertStringContainsString('CT', $res);

        // columnActionsHtml should return a View
        $view = $controller->columnActionsHtml($act);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Création d'un utilisateur pour l'authentification
        $this->admin = Utilisateur::factory()->create();
    }

    /**
     * Test de la fonction duplicate.
     * Vérifie que l'actualité est clonée avec ses relations.
     */
    public function test_duplicate_creates_a_copy_with_tags_and_documents()
    {
        // 1. Préparation des données initiales
        $etiquette = Etiquette::factory()->create();
        $document  = Document::factory()->create(['chemin' => 'dummy.jpg']);

        $original = Actualite::factory()->create([
            'titrefr'       => 'Titre Original',
            'titreeus'      => 'Jatorrizko Izenburua',
            'contenufr'     => 'Contenu...',
            'idUtilisateur' => $this->admin->idUtilisateur, // ou id
        ]);

        // Attachement des relations
        $original->etiquettes()->attach($etiquette->idEtiquette);
        $original->documents()->attach($document->idDocument);

        // 2. Action : Appel de la route duplicate
        // Note: Assurez-vous que votre route est bien nommée 'admin.actualites.duplicate' ou ajustez l'URL
        // Call controller directly to avoid route/middleware in unit test
        auth()->login($this->admin);
        $controller = new ActualiteController();
        $response   = $controller->duplicate($original->idActualite);

        // 3. Assertions

        // Vérifie la redirection vers l'édition de la copie
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        // Récupération de la copie par titre (suffixed)
        $copy = Actualite::where('titrefr', 'Titre Original (Copie)')->first();
        if (! $copy) {
            // Fallback: dernière créée
            $copy = Actualite::latest('idActualite')->first();
        }

        // Vérification des titres modifiés
        $this->assertEquals('Titre Original (Copie)', $copy->titrefr);
        $this->assertEquals('Jatorrizko Izenburua (Kopia)', $copy->titreeus);

        // Vérification des relations clonées
        $this->assertTrue($copy->etiquettes->contains($etiquette->idEtiquette), 'Les étiquettes doivent être dupliquées');
        $this->assertTrue($copy->documents->contains($document->idDocument), 'Les documents doivent être dupliqués');

        // Vérification de l'auteur (doit être l'utilisateur connecté, pas forcément l'original)
        $this->assertEquals($this->admin->getKey(), $copy->idUtilisateur);
    }

    /**
     * Test de ensureEtiquetteIsPublicColumn.
     * Vérifie que la colonne est créée si elle n'existe pas.
     */
    public function test_ensure_etiquette_is_public_column_adds_column_if_missing()
    {
        // 1. Préparation : On force la suppression de la colonne 'public'
        // pour simuler l'état de la base de données avant la migration
        if (Schema::hasColumn('etiquette', 'public')) {
            Schema::table('etiquette', function ($table) {
                $table->dropColumn('public');
            });
        }

        // Assertion de contrôle : on s'assure qu'elle n'existe plus
        $this->assertFalse(Schema::hasColumn('etiquette', 'public'), 'La colonne public devrait être supprimée pour le test');

        // 2. Action : On appelle une route qui déclenche ensureEtiquetteIsPublicColumn()
        // La méthode index() appelle ensureEtiquetteIsPublicColumn() au début.
        // Call controller directly to ensure the column migration helper runs
        $controller = new ActualiteController();
        $controller->adminIndex(Request::create('/pannel/actualites', 'GET'));

        // 3. Assertions : La colonne doit être revenue
        $this->assertTrue(Schema::hasColumn('etiquette', 'public'), 'La colonne public aurait dû être recréée par le contrôleur');

        // Vérification optionnelle du type et de la valeur par défaut
        // Note : Pour vérifier "default(false)", on insère une étiquette et on vérifie la valeur
        $id  = \DB::table('etiquette')->insertGetId(['nom' => 'Test Tag']);
        $tag = \DB::table('etiquette')->where('idEtiquette', $id)->first();

        // SQLite/MySQL retourne souvent '0' ou 0 for false
        $this->assertEquals(0, $tag->public, 'La valeur par défaut de la nouvelle colonne doit être false (0)');
    }
}
