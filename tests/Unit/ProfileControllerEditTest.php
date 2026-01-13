<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\DocumentObligatoire;
use App\Models\Document;

class ProfileControllerEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_populates_documents_obligatoires_when_user_has_roles()
    {
        $user = Utilisateur::factory()->create();

        // create a role and link it to the user via the avoir pivot
        $role = Role::create(['name' => 'tester', 'guard_name' => 'web']);
        DB::table('avoir')->insert([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
            'model_type' => Utilisateur::class,
        ]);

        // create a document obligatoire and link to role via attribuer pivot
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 1;
        $docOblig->nom = 'ID Card';
        $docOblig->save();
        DB::table('attribuer')->insert([
            'idRole' => $role->idRole,
            'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
        ]);

        // create a document for the user matching the docOblig name
        $document = Document::create(['nom' => 'ID Card - scan', 'chemin' => 'path', 'type' => 'pdf', 'etat' => 'valide']);
        // attach to user via contenir pivot
        DB::table('contenir')->insert([
            'idDocument' => $document->idDocument,
            'idUtilisateur' => $user->idUtilisateur,
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(function () use ($user) { return $user; });

        $controller = new \App\Http\Controllers\ProfileController();
        $view = $controller->edit($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);

        $data = $view->getData();
        $this->assertArrayHasKey('documentsObligatoires', $data);
        $docs = $data['documentsObligatoires'];
        $this->assertNotEmpty($docs);

        // first doc should have etat mapped from document etat 'valide' -> 'valide'
        $first = $docs->first();
        $this->assertEquals('valide', $first->etat);
        $this->assertNotNull($first->documentUploaded);
    }

    public function test_edit_marks_document_non_remis_when_user_has_no_uploaded_document()
    {
        $user = Utilisateur::factory()->create();

        $role = Role::create(['name' => 'tester2', 'guard_name' => 'web']);
        DB::table('avoir')->insert([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
            'model_type' => Utilisateur::class,
        ]);

        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 2;
        $docOblig->nom = 'Passport';
        $docOblig->save();
        DB::table('attribuer')->insert([
            'idRole' => $role->idRole,
            'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(function () use ($user) { return $user; });

        $controller = new \App\Http\Controllers\ProfileController();
        $view = $controller->edit($request);

        $data = $view->getData();
        $docs = $data['documentsObligatoires'];
        $this->assertNotEmpty($docs);

        $first = $docs->first();
        $this->assertEquals('non_remis', $first->etat);
        $this->assertNull($first->documentUploaded);
    }
}
