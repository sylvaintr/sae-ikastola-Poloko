<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\DB;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Document;
use App\Models\DocumentObligatoire;

class AccountControllerShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_executes_documents_obligatoires_branch_when_user_has_roles()
    {
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag());

        // Create role and document obligatoire, and link them via pivot 'attribuer'
        $role = Role::create(['name' => 'ROLE_DOC', 'guard_name' => 'web']);
        $docObligId = 1;
        DB::table('documentObligatoire')->insert(['idDocumentObligatoire' => $docObligId, 'nom' => 'Piece ID']);
        $docOblig = DocumentObligatoire::find($docObligId);
        DB::table('attribuer')->insert(['idRole' => $role->idRole, 'idDocumentObligatoire' => $docObligId]);

        // Create user and attach role
        $user = Utilisateur::factory()->create(['prenom' => 'Doc', 'nom' => 'Owner']);
        $user->rolesCustom()->attach([$role->idRole => ['model_type' => Utilisateur::class]]);

        // Create a document for the user matching the DocumentObligatoire name
        Storage::fake('public');
        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/piece_id.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create(['chemin' => $path, 'nom' => 'Piece ID - piece_id.pdf', 'etat' => 'actif']);
        DB::table('contenir')->insert(['idUtilisateur' => $user->idUtilisateur, 'idDocument' => $document->idDocument]);

        // Call controller method directly to avoid blade route() generation during full view render
        $controller = new \App\Http\Controllers\Admin\AccountController();
        $view = $controller->show($user);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);

        $data = $view->getData();
        $docs = $data['documentsObligatoiresAvecEtat'];
        $this->assertNotEmpty($docs);

        $first = $docs->first();
        $this->assertEquals('remis', $first->etat);
        $this->assertNotNull($first->documentUploaded);
        $this->assertNotNull($first->dateRemise);
    }

    public function test_show_sets_non_remis_when_no_user_document_found()
    {
        $this->withoutMiddleware();
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        // Create role and document obligatoire, link via pivot
        $role = \App\Models\Role::create(['name' => 'ROLE_ND', 'guard_name' => 'web']);
        $docId = 5;
        DB::table('documentObligatoire')->insert(['idDocumentObligatoire' => $docId, 'nom' => 'No File Doc']);
        DB::table('attribuer')->insert(['idRole' => $role->idRole, 'idDocumentObligatoire' => $docId]);

        // Create a user and attach role but do NOT create any matching Document
        $user = \App\Models\Utilisateur::factory()->create();
        $user->rolesCustom()->attach([$role->idRole => ['model_type' => \App\Models\Utilisateur::class]]);

        // Call controller->show directly
        $controller = new \App\Http\Controllers\Admin\AccountController();
        $view = $controller->show($user);
        $data = $view->getData();

        $docs = $data['documentsObligatoiresAvecEtat'];
        $this->assertNotEmpty($docs);

        $first = $docs->first();
        $this->assertEquals('non_remis', $first->etat);
        $this->assertNull($first->documentUploaded);
        $this->assertNull($first->dateRemise);
    }

    public function test_show_sets_null_dateRemise_when_file_missing()
    {
        $this->withoutMiddleware();
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        // Create role and document obligatoire, link via pivot
        $role = \App\Models\Role::create(['name' => 'ROLE_MISS', 'guard_name' => 'web']);
        $docId = 7;
        DB::table('documentObligatoire')->insert(['idDocumentObligatoire' => $docId, 'nom' => 'MissingFileDoc']);
        DB::table('attribuer')->insert(['idRole' => $role->idRole, 'idDocumentObligatoire' => $docId]);

        // Create user and attach role
        $user = \App\Models\Utilisateur::factory()->create();
        $user->rolesCustom()->attach([$role->idRole => ['model_type' => \App\Models\Utilisateur::class]]);

        // Fake storage but DO NOT put the file (so exists() returns false)
        Storage::fake('public');
        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/missing.pdf';

        // Create document record pointing to a non-existing file
        $document = \App\Models\Document::factory()->create(['chemin' => $path, 'nom' => 'MissingFileDoc - missing.pdf', 'etat' => 'actif']);
        DB::table('contenir')->insert(['idUtilisateur' => $user->idUtilisateur, 'idDocument' => $document->idDocument]);

        // Call controller->show directly
        $controller = new \App\Http\Controllers\Admin\AccountController();
        $view = $controller->show($user);
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);

        $data = $view->getData();
        $docs = $data['documentsObligatoiresAvecEtat'];
        $this->assertNotEmpty($docs);

        $first = $docs->first();
        $this->assertNotNull($first->documentUploaded);
        $this->assertNull($first->dateRemise, 'Expected dateRemise to be null when file is missing');
    }
}
