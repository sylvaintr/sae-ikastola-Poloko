<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ActualiteController;
use App\Models\Actualite;
use App\Models\Document;
use App\Models\Etiquette;
use App\Models\Utilisateur;

class ActualiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Ensure storage is faked for file operations
        Storage::fake('public');
    }

    public function test_index_returns_view_with_actualites_and_etiquettes()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $et = Etiquette::factory()->create();
        $act = Actualite::factory()->create(['type' => 'private', 'dateP' => now()]);

        $controller = new ActualiteController();
        $response = $controller->index();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $data = $response->getData();
        $this->assertArrayHasKey('actualites', $data);
        $this->assertArrayHasKey('etiquettes', $data);
    }

    public function test_store_creates_actualite_and_documents()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $et = Etiquette::factory()->create();

        $file = UploadedFile::fake()->image('photo.jpg');

        $params = [
            'type' => 'public',
            'dateP' => now()->toDateString(),
            'titrefr' => 'Titre FR',
            'descriptionfr' => 'Desc FR',
            'descriptioneus' => 'Desc EUS',
            'contenufr' => 'Contenu FR',
            'contenueus' => 'Contenu EUS',
            'etiquettes' => [$et->idEtiquette],
        ];

        $request = Request::create('/actualites', 'POST', $params, [], ['images' => [$file]]);

        $controller = new ActualiteController();
        $resp = $controller->store($request);

        $this->assertTrue(Actualite::where('contenufr', 'Contenu FR')->exists());
        $this->assertTrue(Document::where('nom', 'photo.jpg')->exists());
        $actualite = Actualite::where('contenufr', 'Contenu FR')->first();
        $this->assertGreaterThan(0, $actualite->documents()->count());
    }

    public function test_show_returns_view()
    {
        $act = Actualite::factory()->create(['dateP' => now()]);

        $controller = new ActualiteController();
        $response = $controller->show($act->idActualite);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $data = $response->getData();
        $this->assertArrayHasKey('actualite', $data);
    }

    public function test_edit_returns_view_or_redirect_if_missing()
    {
        $act = Actualite::factory()->create(['dateP' => now()]);
        $controller = new ActualiteController();
        $response = $controller->edit($act->idActualite);

        // edit returns a View when found
        $this->assertTrue($response instanceof \Illuminate\View\View || $response instanceof \Illuminate\Http\RedirectResponse);
    }

    public function test_update_updates_and_adds_document()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $act = Actualite::factory()->create(['dateP' => now(), 'idUtilisateur' => $user->idUtilisateur]);

        $file = UploadedFile::fake()->image('new.jpg');
        $params = [
            'titrefr' => 'Updated',
            'descriptionfr' => 'Desc FR',
            'titreeus' => 'T EUS',
            'descriptioneus' => 'Desc EUS',
            'contenueus' => 'Contenu EUS',
            'contenufr' => 'Contenu FR',
            'type' => 'public',
            'dateP' => now()->toDateString(),
        ];
        $request = Request::create('/actualites/'.$act->idActualite, 'PUT', $params, [], ['images' => [$file]]);

        $controller = new ActualiteController();
        $resp = $controller->update($request, $act->idActualite);

        $this->assertEquals('Updated', Actualite::find($act->idActualite)->titrefr);
        $this->assertTrue(Document::where('nom', 'new.jpg')->exists());
    }

    public function test_detach_document_removes_file_and_pivot()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $act = Actualite::factory()->create(['dateP' => now(), 'idUtilisateur' => $user->idUtilisateur]);
        $doc = Document::factory()->create(['chemin' => 'actualites/to_delete.jpg', 'nom' => 'to_delete.jpg']);

        // Ensure file exists in fake storage
        Storage::disk('public')->put($doc->chemin, 'contents');

        // Attach pivot
        $act->documents()->attach($doc->idDocument);

        $controller = new ActualiteController();
        $resp = $controller->detachDocument($act->idActualite, $doc->idDocument);

        $this->assertFalse($act->documents()->where('document.idDocument', $doc->idDocument)->exists());
        $this->assertFalse(Storage::disk('public')->exists($doc->chemin));
    }

    public function test_destroy_deletes_actualite_and_documents()
    {
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $act = Actualite::factory()->create(['dateP' => now(), 'idUtilisateur' => $user->idUtilisateur]);
        $doc = Document::factory()->create(['chemin' => 'actualites/del.jpg', 'nom' => 'del.jpg']);
        Storage::disk('public')->put($doc->chemin, 'data');
        $act->documents()->attach($doc->idDocument);

        $controller = new ActualiteController();
        $resp = $controller->destroy($act->idActualite);

        $this->assertFalse(Actualite::where('idActualite', $act->idActualite)->exists());
        $this->assertFalse(Document::where('idDocument', $doc->idDocument)->exists());
        $this->assertFalse(Storage::disk('public')->exists($doc->chemin));
    }

    public function test_admin_index_returns_view()
    {
        $act = Actualite::factory()->create(['dateP' => now()]);
        $controller = new ActualiteController();
        $response = $controller->adminIndex();
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $data = $response->getData();
        $this->assertArrayHasKey('actualites', $data);
    }

    public function test_page_create(){

        $this->get('/actualites/create')
            ->assertStatus(200)
            ->assertViewIs('actualites.create');

    }

    public function test_page_index(){

        $this->get('/actualites')
            ->assertStatus(200)
            ->assertViewIs('actualites.index');

    }

    public function test_page_fail_edit_not_found(){

        $this->get('/actualites/9999/edit')
            ->assertStatus(302); // Redirect since not found

    }

    public function test_update_don_t_find_actualite(){

        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $params = [
            'titrefr' => 'Updated',
            'descriptionfr' => 'Desc FR',
            'titreeus' => 'T EUS',
            'descriptioneus' => 'Desc EUS',
            'contenueus' => 'Contenu EUS',
            'contenufr' => 'Contenu FR',
            'type' => 'public',
            'dateP' => now()->toDateString(),
        ];
        $request = Request::create('/actualites/9999', 'PUT', $params);

        $controller = new ActualiteController();
        $resp = $controller->update($request, 9999);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_metre_a_jour_actualite_avec_etiquette(){
        $user = Utilisateur::factory()->create();
        Auth::login($user);

        $et = Etiquette::factory()->create();
        $act = Actualite::factory()->create(['dateP' => now(), 'idUtilisateur' => $user->idUtilisateur]);
        $act->etiquettes()->attach($et->idEtiquette);

        $params = [
            'titrefr' => 'Updated with Etiquette',
            'descriptionfr' => 'Desc FR',
            'titreeus' => 'T EUS',
            'descriptioneus' => 'Desc EUS',
            'contenueus' => 'Contenu EUS',
            'contenufr' => 'Contenu FR',
            'type' => 'public',
            'dateP' => now()->toDateString(),
            'etiquettes' => [$et->idEtiquette],
        ];
        $request = Request::create('/actualites/'.$act->idActualite, 'PUT', $params);

        $controller = new ActualiteController();
        $resp = $controller->update($request, $act->idActualite);

        $this->assertEquals('Updated with Etiquette', Actualite::find($act->idActualite)->titrefr);
        $this->assertCount(1, Actualite::find($act->idActualite)->etiquettes()->get());
    }

        
}




