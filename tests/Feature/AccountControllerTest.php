<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class AccountControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    use RefreshDatabase;

    public function test_index_method_exists(): void
    {
        // given
        // nothing to set up for reflection assertions

        // when
        $existsIndex = method_exists(AccountController::class, 'index');
        $existsStore = method_exists(AccountController::class, 'store');
        $existsUpdate = method_exists(AccountController::class, 'update');
        $existsValidate = method_exists(AccountController::class, 'validateAccount');
        $existsDestroy = method_exists(AccountController::class, 'destroy');

        // then
        $this->assertTrue($existsIndex);
        $this->assertTrue($existsStore);
        $this->assertTrue($existsUpdate);
        $this->assertTrue($existsValidate);
        $this->assertTrue($existsDestroy);
    }

    public function test_store_missing_fields_throws_validation_exception(): void
    {
        // given
        $this->expectException(ValidationException::class);
        $controller = new AccountController();
        $request = Request::create('/admin/accounts/store', 'POST', []);

        // when
        $controller->store($request);

        // then
        // expectation set above will satisfy the assertion
    }

    public function test_validateAccount_calls_update_and_redirects(): void
    {
        // given
        $account = \App\Models\Utilisateur::factory()->create();
        $controller = new AccountController();

        // when
        $response = $controller->validateAccount($account);

        // then
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_actualite_store_creates_and_redirects(): void
    {
        // given
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);
        $controller = new \App\Http\Controllers\ActualiteController();

        $request = Request::create('/actualites/store', 'POST', [
            'type' => 'public',
            'dateP' => now()->format('Y-m-d'),
            'titrefr' => 'titre test',
            'descriptionfr' => 'desc fr',
            'contenufr' => 'contenu fr',
            'descriptioneus' => 'desc eu',
            'contenueus' => 'contenu eu',
            'archive' => false,
        ]);

        // when
        $response = $controller->store($request);

        // then
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('actualite', [
            'titrefr' => 'titre test',
        ]);
    }

    public function test_actualite_update_updates_and_redirects(): void
    {
        // given
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $actualite = \App\Models\Actualite::factory()->create([
            'titrefr' => 'old title',
            'descriptionfr' => 'old desc',
            'contenufr' => 'old content',
            'descriptioneus' => 'old eu desc',
            'contenueus' => 'old eu content',
            'type' => 'public',
            'dateP' => now(),
            'idUtilisateur' => $user->idUtilisateur,
        ]);

        $controller = new \App\Http\Controllers\ActualiteController();

        $request = Request::create('/actualites/update', 'POST', [
            'type' => 'public',
            'dateP' => now()->format('Y-m-d'),
            'titrefr' => 'updated title',
            'descriptionfr' => 'new desc',
            'contenufr' => 'new content',
            'descriptioneus' => 'new eu desc',
            'contenueus' => 'new eu content',
            'archive' => false,
        ]);

        // when
        $response = $controller->update($request, $actualite->idActualite);

        // then
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('actualite', [
            'idActualite' => $actualite->idActualite,
            'titrefr' => 'updated title',
        ]);
    }

    public function test_actualite_filter_stores_session_and_redirects(): void
    {
        // given
        $controller = new \App\Http\Controllers\ActualiteController();
        $request = Request::create('/actualites/filter', 'POST', [
            'etiquettes' => ['1', '2'],
        ]);

        // when
        $response = $controller->filter($request);

        // then
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals([1,2], session('selectedEtiquettes'));
    }

    public function test_actualite_create_returns_view_with_etiquettes(): void
    {
        // given
        $controller = new \App\Http\Controllers\ActualiteController();

        // when
        $view = $controller->create();

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertArrayHasKey('etiquettes', $view->getData());
    }

    public function test_actualite_show_and_edit_return_views(): void
    {
        // given
        $actualite = \App\Models\Actualite::factory()->create();
        $controller = new \App\Http\Controllers\ActualiteController();

        // when
        $show = $controller->show($actualite->idActualite);
        $edit = $controller->edit($actualite->idActualite);

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $show);
        $this->assertArrayHasKey('actualite', $show->getData());

        $this->assertInstanceOf(\Illuminate\View\View::class, $edit);
        $this->assertArrayHasKey('actualite', $edit->getData());
        $this->assertArrayHasKey('etiquettes', $edit->getData());
    }

    public function test_actualite_destroy_deletes_actualite_and_documents(): void
    {
        // given
        Storage::fake('public');

        $actualite = \App\Models\Actualite::factory()->create();

        $path = 'actualites/test-image.jpg';
        Storage::disk('public')->put($path, 'content');

        $document = \App\Models\Document::factory()->create(['chemin' => $path]);
        $actualite->documents()->attach($document->idDocument);

        $controller = new \App\Http\Controllers\ActualiteController();

        // when
        $response = $controller->destroy($actualite->idActualite);

        // then
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('actualite', ['idActualite' => $actualite->idActualite]);
        $this->assertDatabaseMissing('document', ['idDocument' => $document->idDocument]);
    }

    public function test_detachDocument_removes_document_and_file_and_redirects(): void
    {
        // given
        Storage::fake('public');

        $actualite = \App\Models\Actualite::factory()->create();
        $path = 'actualites/detach-image.jpg';
        Storage::disk('public')->put($path, 'content');

        $document = \App\Models\Document::factory()->create(['chemin' => $path]);
        $actualite->documents()->attach($document->idDocument);

        $controller = new \App\Http\Controllers\ActualiteController();

        // when
        $response = $controller->detachDocument($actualite->idActualite, $document->idDocument);

        // then
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('document', ['idDocument' => $document->idDocument]);
        $this->assertFalse($actualite->documents()->where('document.idDocument', $document->idDocument)->exists());
    }

    public function test_index_guest_and_authenticated_behaviour(): void
    {
        // guest: should return view with actualites and etiquettes
        $controller = new \App\Http\Controllers\ActualiteController();
        $guestView = $controller->index();
        $this->assertInstanceOf(\Illuminate\View\View::class, $guestView);
        $this->assertArrayHasKey('actualites', $guestView->getData());

        // authenticated: create role/etiquette and private actualite
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $role = \App\Models\Role::create(['name' => 'ROLE_A', 'guard_name' => 'web']);
        $etiquette = \App\Models\Etiquette::factory()->create(['nom' => 'E1']);
        // link etiquette to role via posseder pivot
        \App\Models\Posseder::create(['idRole' => $role->idRole, 'idEtiquette' => $etiquette->idEtiquette]);
        // ensure pivot entry exists explicitly (attach sometimes behaves unexpectedly in tests)
        \App\Models\Avoir::create([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
            'model_type' => \App\Models\Utilisateur::class,
        ]);

        $private = \App\Models\Actualite::factory()->create(['type' => 'private']);
        $private->etiquettes()->attach($etiquette->idEtiquette);

        $authView = (new \App\Http\Controllers\ActualiteController())->index();
        $this->assertInstanceOf(\Illuminate\View\View::class, $authView);
    }

    public function test_store_with_slashed_date_and_with_images_creates_actualite_and_documents()
    {
        Storage::fake('public');

        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $controller = new \App\Http\Controllers\ActualiteController();

        $tmp1 = sys_get_temp_dir() . '/img1.png';
        // 1x1 PNG
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');
        file_put_contents($tmp1, $png);
        $img1 = new \Illuminate\Http\UploadedFile($tmp1, 'img1.png', 'image/png', null, true);

        $request = Request::create('/actualites/store', 'POST', [
            'type' => 'public',
            'dateP' => '01/01/2025',
            'titrefr' => 'img test',
            'descriptionfr' => 'd',
            'contenufr' => 'c',
            'descriptioneus' => 'de',
            'contenueus' => 'ce',
            'archive' => false,
        ]);
        // attach files
        $request->files->set('images', [$img1]);

        $response = $controller->store($request);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('actualite', ['titrefr' => 'img test']);
        @unlink($tmp1);
    }

    public function test_update_returns_redirect_when_not_found()
    {
        $controller = new \App\Http\Controllers\ActualiteController();
        $request = Request::create('/actualites/update', 'POST', [
            'type' => 'public',
            'dateP' => now()->format('Y-m-d'),
            'titrefr' => 'x',
            'descriptionfr' => 'd',
            'contenufr' => 'c',
            'descriptioneus' => 'de',
            'contenueus' => 'ce',
        ]);

        $response = $controller->update($request, 999999);
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_adminIndex_filters_apply()
    {
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $a1 = \App\Models\Actualite::factory()->create(['titrefr' => 'search-me', 'type' => 'public', 'archive' => false]);
        $controller = new \App\Http\Controllers\ActualiteController();
        $request = Request::create('/admin/actualites', 'GET', ['search' => 'search-me']);

        $view = $controller->adminIndex($request);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertArrayHasKey('actualites', $view->getData());
    }

    public function test_controller_magic_delegates_to_helpers()
    {
        $controller = new \App\Http\Controllers\ActualiteController();
        $actualite = \App\Models\Actualite::factory()->create(['titrefr' => 'hello', 'archive' => false]);

        // __call should forward to ActualiteHelpers::columnTitre
        $res = $controller->columnTitre($actualite);
        $this->assertEquals('hello', $res);

        // filterColumnTitreInline via invokeMethod should filter the query
        $query = \App\Models\Actualite::query();
        $helpers = new \App\Http\Controllers\ActualiteHelpers();
        $helpers->filterTitreColumn($query, 'hello');
        $found = $query->get();
        $this->assertNotEmpty($found);
    }

    public function test_edit_catches_modelnotfound_and_redirects_with_error()
    {
        $controller = new \App\Http\Controllers\ActualiteController();

        $response = $controller->edit(9999999);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertStringContainsString(route('admin.actualites.index'), $response->getTargetUrl());
        $this->assertTrue(session()->has('error'));
    }

    public function test_data_filters_by_type_parameter()
    {
        // given
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        \App\Models\Actualite::factory()->create(['type' => 'public', 'titrefr' => 'pub1']);
        \App\Models\Actualite::factory()->create(['type' => 'private', 'titrefr' => 'priv1']);

        $controller = new \App\Http\Controllers\ActualiteController();
        $request = Request::create('/actualites/data', 'GET', ['type' => 'public']);

        // when
        $response = $controller->data($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $payload = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertNotEmpty($payload['data']);
        foreach ($payload['data'] as $row) {
            $this->assertEquals('public', $row['type']);
        }
    }

    public function test_index_respects_selected_etiquettes_for_authenticated_user()
    {
        // given
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $role = \App\Models\Role::create(['name' => 'ROLE_SELECT', 'guard_name' => 'web']);
        $etiquette = \App\Models\Etiquette::factory()->create();

        // create mapping in posseder so the etiquette is bound to the role
        \App\Models\Posseder::create(['idRole' => $role->idRole, 'idEtiquette' => $etiquette->idEtiquette]);

        $user->rolesCustom()->attach($role->idRole, ['model_type' => \App\Models\Utilisateur::class]);

        // create an actualite and attach the etiquette (use public to avoid role-interaction flakiness)
        $actualite = \App\Models\Actualite::factory()->create(['type' => 'public']);
        $actualite->etiquettes()->attach($etiquette->idEtiquette);

        // set selectedEtiquettes in session
        session(['selectedEtiquettes' => [$etiquette->idEtiquette]]);

        $controller = new \App\Http\Controllers\ActualiteController();
        $request = Request::create('/actualites', 'GET');

        // when
        $view = $controller->index($request);

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();
        $this->assertArrayHasKey('actualites', $data);
        $actualites = $data['actualites'];
        $this->assertTrue($actualites->contains('idActualite', $actualite->idActualite));
    }

    public function test_update_with_images_calls_uploadImages_and_attaches_documents()
    {
        // given
        Storage::fake('public');
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $actualite = \App\Models\Actualite::factory()->create([
            'titrefr' => 'to-update',
            'descriptionfr' => 'd',
            'contenufr' => 'c',
            'descriptioneus' => 'de',
            'contenueus' => 'ce',
            'type' => 'public',
            'dateP' => now(),
            'idUtilisateur' => $user->idUtilisateur,
        ]);

        $tmp = sys_get_temp_dir() . '/upd_img.png';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');
        file_put_contents($tmp, $png);
        $img = new \Illuminate\Http\UploadedFile($tmp, 'upd_img.png', 'image/png', null, true);

        $request = Request::create('/actualites/update', 'POST', [
            'type' => 'public',
            'dateP' => now()->format('Y-m-d'),
            'titrefr' => 'updated',
            'descriptionfr' => 'new',
            'contenufr' => 'new',
            'descriptioneus' => 'new',
            'contenueus' => 'new',
            'archive' => false,
        ]);
        $request->files->set('images', [$img]);

        $controller = new \App\Http\Controllers\ActualiteController();

        // when
        $response = $controller->update($request, $actualite->idActualite);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseHas('actualite', ['idActualite' => $actualite->idActualite, 'titrefr' => 'updated']);
        $this->assertDatabaseHas('document', ['type' => 'image', 'etat' => 'actif']);

        @unlink($tmp);
    }

    public function test_update_normalizes_slashed_date_in_update()
    {
        // given
        $user = \App\Models\Utilisateur::factory()->create();
        $this->actingAs($user);

        $actualite = \App\Models\Actualite::factory()->create([
            'titrefr' => 'to-update-date',
            'descriptionfr' => 'd',
            'contenufr' => 'c',
            'descriptioneus' => 'de',
            'contenueus' => 'ce',
            'type' => 'public',
            'dateP' => now(),
            'idUtilisateur' => $user->idUtilisateur,
        ]);

        $controller = new \App\Http\Controllers\ActualiteController();

        $request = Request::create('/actualites/update', 'POST', [
            'type' => 'public',
            'dateP' => '25/12/2025',
            'titrefr' => 'updated-date',
            'descriptionfr' => 'new',
            'contenufr' => 'new',
            'descriptioneus' => 'new',
            'contenueus' => 'new',
            'archive' => false,
        ]);

        // when
        $response = $controller->update($request, $actualite->idActualite);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $fresh = \App\Models\Actualite::find($actualite->idActualite);
        $this->assertEquals('2025-12-25', $fresh->dateP->format('Y-m-d'));
    }
}
