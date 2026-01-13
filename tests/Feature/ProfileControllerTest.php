<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\Document;
use App\Models\DocumentObligatoire;
use App\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mise_a_jour_reinitialise_verif_email_et_redirige()
    {
        $user = Utilisateur::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'email' => 'new@example.com',
                'nom' => $user->nom,
                'prenom' => $user->prenom,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('utilisateur', [
            'idUtilisateur' => $user->idUtilisateur,
            'email' => 'new@example.com',
            'email_verified_at' => null,
        ]);
    }

    public function test_suppression_exige_mot_de_passe_et_supprime_utilisateur()
    {
        $user = Utilisateur::factory()->create([
            'password' => 'password',
        ]);

        // Ensure password matches the accessor/mutator used by model
        $this->assertTrue(password_verify('password', $user->getAuthPassword()));

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('utilisateur', [
            'idUtilisateur' => $user->idUtilisateur,
        ]);
    }

    public function test_suppression_document_supprime_fichier_et_detache_document()
    {
        Storage::fake('public');

        $user = Utilisateur::factory()->create();

        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/test.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create([
            'chemin' => $path,
            'etat' => 'actif',
        ]);

        // attach document to user via pivot
        $user->documents()->attach($document->idDocument);

        $this->actingAs($user)
            ->delete(route('profile.document.delete', ['document' => $document->idDocument]))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'document-deleted');

        Storage::disk('public')->assertMissing($path);

        $this->assertFalse($user->documents()->where('document.idDocument', $document->idDocument)->exists());

        $this->assertDatabaseMissing('document', [
            'idDocument' => $document->idDocument,
        ]);
    }

    public function test_edit_retourne_vue_avec_utilisateur_et_documents()
    {
        $user = Utilisateur::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertViewIs('profile.edit')
            ->assertViewHas('user')
            ->assertViewHas('documentsObligatoires');
    }

    public function test_upload_document_echec_validation_retourne_erreurs()
    {
        $user = Utilisateur::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.document.upload'), [])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors();
    }

    public function test_telechargement_document_interdit_si_pas_proprietaire()
    {
        Storage::fake('public');

        $owner = Utilisateur::factory()->create(['prenom' => 'Owner', 'nom' => 'User']);
        $other = Utilisateur::factory()->create(['prenom' => 'Other', 'nom' => 'User']);

        $path = 'profiles/' . $owner->idUtilisateur . '/obligatoires/test.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create([
            'chemin' => $path,
            'nom' => 'Doc - test.pdf',
        ]);

        $owner->documents()->attach($document->idDocument);

        $this->actingAs($other)
            ->get(route('profile.document.download', ['document' => $document->idDocument]))
            ->assertStatus(403);
    }

    public function test_telechargement_document_succes_retourne_fichier()
    {
        Storage::fake('public');

        $user = Utilisateur::factory()->create(['prenom' => 'Jean', 'nom' => 'Dupont']);

        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/test.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create([
            'chemin' => $path,
            'nom' => 'Contrat - test.pdf',
        ]);

        $user->documents()->attach($document->idDocument);

        $response = $this->actingAs($user)
            ->get(route('profile.document.download', ['document' => $document->idDocument]));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_upload_document_succes_stocke_et_attache()
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_TEST', 'guard_name' => 'web']);

        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 1;
        $docOblig->nom = 'Contrat';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $tmpPath = sys_get_temp_dir() . '/test.pdf';
        file_put_contents($tmpPath, "%PDF-1.4\n%âãÏÓ\n");
        $uploaded = new UploadedFile($tmpPath, 'contract.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'document-uploaded');

        // ensure a Document record was created and attached
        $this->assertDatabaseHas('document', [
            'type' => 'doc',
        ]);
        unlink($tmpPath);
    }

    public function test_upload_document_non_uploadable_retourne_erreur()
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_TEST2', 'guard_name' => 'web']);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 2;
        $docOblig->nom = 'Contrat2';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        // create existing document with etat 'valide'
        $existing = Document::factory()->create(['etat' => 'valide', 'nom' => 'Contrat2 - old.pdf']);
        $user->documents()->attach($existing->idDocument);

        $tmpPath = sys_get_temp_dir() . '/test2.pdf';
        file_put_contents($tmpPath, "%PDF-1.4\n%âãÏÓ\n");
        $uploaded = new UploadedFile($tmpPath, 'contract2.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        unlink($tmpPath);
    }

    public function test_upload_document_magic_bytes_invalide_retourne_erreur()
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_TEST3', 'guard_name' => 'web']);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 3;
        $docOblig->nom = 'Contrat3';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $tmpPath = sys_get_temp_dir() . '/bad.pdf';
        file_put_contents($tmpPath, "BADBYTES");
        $uploaded = new UploadedFile($tmpPath, 'bad.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        unlink($tmpPath);
    }

    public function test_upload_document_extension_invalide_retourne_erreur()
    {
        Storage::fake('public');

        $user = Utilisateur::factory()->create();

        $tmpPath = sys_get_temp_dir() . '/bad.exe';
        file_put_contents($tmpPath, "MZ\x00\x00");
        $uploaded = new UploadedFile($tmpPath, 'bad.exe', 'application/octet-stream', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => 9999,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors();
        unlink($tmpPath);
    }

    public function test_suppression_document_interdite_si_pas_proprietaire()
    {
        $owner = Utilisateur::factory()->create();
        $other = Utilisateur::factory()->create();

        $document = Document::factory()->create(['etat' => 'actif']);
        $owner->documents()->attach($document->idDocument);

        $this->actingAs($other)
            ->delete(route('profile.document.delete', ['document' => $document->idDocument]))
            ->assertStatus(403);
    }

    public function test_suppression_document_ne_peut_pas_supprimer_valide()
    {
        Storage::fake('public');

        $user = Utilisateur::factory()->create();
        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/valid.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create([
            'chemin' => $path,
            'etat' => 'valide',
        ]);

        $user->documents()->attach($document->idDocument);

        $this->actingAs($user)
            ->delete(route('profile.document.delete', ['document' => $document->idDocument]))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('error');

        // file should remain
        Storage::disk('public')->assertExists($path);
    }
    
    public function test_upload_docx_sans_dossier_word_retourne_erreur()
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_DOCX', 'guard_name' => 'web']);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 10;
        $docOblig->nom = 'DocxReq';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        // create a zip file without word/ folder
        $tmp = sys_get_temp_dir() . '/no_word.docx';
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('docProps/core.xml', 'content');
        $zip->close();

        $uploaded = new UploadedFile($tmp, 'no_word.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        @unlink($tmp);
    }

    public function test_upload_fichier_vide_impossible_lecture_retourne_erreur()
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_EMPTY', 'guard_name' => 'web']);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 11;
        $docOblig->nom = 'EmptyReq';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $tmp = sys_get_temp_dir() . '/empty.pdf';
        file_put_contents($tmp, '');
        $uploaded = new UploadedFile($tmp, 'empty.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        @unlink($tmp);
    }

    public function test_upload_document_pas_pour_roles_utilisateur_retourne_erreur()
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_OTHER', 'guard_name' => 'web']);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 12;
        $docOblig->nom = 'NotForUser';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        // user has no roles attached

        $tmp = sys_get_temp_dir() . '/some.pdf';
        file_put_contents($tmp, "%PDF-1.4\n%\n");
        $uploaded = new UploadedFile($tmp, 'some.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        @unlink($tmp);
    }

    public function test_telechargement_document_fichier_manquant_retourne_404()
    {
        $user = Utilisateur::factory()->create(['prenom' => 'X', 'nom' => 'Y']);
        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/missing.pdf';
        $document = Document::factory()->create(['chemin' => $path, 'nom' => 'Missing - missing.pdf']);
        $user->documents()->attach($document->idDocument);

        $this->actingAs($user)
            ->get(route('profile.document.download', ['document' => $document->idDocument]))
            ->assertStatus(404);
    }
}
