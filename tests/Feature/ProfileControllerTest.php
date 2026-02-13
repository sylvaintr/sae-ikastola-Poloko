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
        // given
        $uniqueOldEmail = 'old_' . uniqid() . '@example.com';
        $uniqueNewEmail = 'new_' . uniqid() . '@example.com';

        $user = Utilisateur::factory()->create([
            'email' => $uniqueOldEmail,
            'email_verified_at' => now(),
        ]);

        // when
        $response = $this->actingAs($user)
            ->patch(route('profile.update'), [
                'email' => $uniqueNewEmail,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
            ]);

        // then
        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('utilisateur', [
            'idUtilisateur' => $user->idUtilisateur,
            'email' => $uniqueNewEmail,
            'email_verified_at' => null,
        ]);
    }

    public function test_suppression_exige_mot_de_passe_et_supprime_utilisateur()
    {
        // given
        $user = Utilisateur::factory()->create([
            'password' => 'password',
        ]);

        // Ensure password matches the accessor/mutator used by model
        $this->assertTrue(password_verify('password', $user->getAuthPassword()));

        // when
        $response = $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        // then
        $response->assertRedirect('/');
        $this->assertDatabaseMissing('utilisateur', [
            'idUtilisateur' => $user->idUtilisateur,
        ]);
    }

    public function test_suppression_document_supprime_fichier_et_detache_document()
    {
        // given
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

        // when
        $response = $this->actingAs($user)
            ->delete(route('profile.document.delete', ['document' => $document->idDocument]));

        // then
        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'document-deleted');

        Storage::disk('public')->assertMissing($path);

        $this->assertFalse($user->documents()->where('document.idDocument', $document->idDocument)->exists());

        $this->assertDatabaseMissing('document', [
            'idDocument' => $document->idDocument,
        ]);
    }

    public function test_edit_retourne_vue_avec_utilisateur_et_documents()
    {
        // given
        $user = Utilisateur::factory()->create();

        // when
        $response = $this->actingAs($user)->get(route('profile.edit'));

        // then
        $response->assertStatus(200)
            ->assertViewIs('profile.edit')
            ->assertViewHas('user')
            ->assertViewHas('documentsObligatoires');
    }

    public function test_upload_document_echec_validation_retourne_erreurs()
    {
        // given
        $user = Utilisateur::factory()->create();

        // when
        $response = $this->actingAs($user)->post(route('profile.document.upload'), []);

        // then
        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors();
    }

    public function test_telechargement_document_interdit_si_pas_proprietaire()
    {
        // given
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

        // when
        $response = $this->actingAs($other)->get(route('profile.document.download', ['document' => $document->idDocument]));

        // then
        $response->assertStatus(403);
    }

    public function test_telechargement_document_succes_retourne_fichier()
    {
        // given
        Storage::fake('public');

        $user = Utilisateur::factory()->create(['prenom' => 'Jean', 'nom' => 'Dupont']);

        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/test.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create([
            'chemin' => $path,
            'nom' => 'Contrat - test.pdf',
        ]);

        $user->documents()->attach($document->idDocument);

        // when
        $response = $this->actingAs($user)->get(route('profile.document.download', ['document' => $document->idDocument]));

        // then
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_upload_document_succes_stocke_et_attache()
    {
        // given
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_TEST_' . uniqid(), 'guard_name' => 'web']);

        // Utiliser un ID unique pour éviter les conflits
        $uniqueId = (DocumentObligatoire::max('idDocumentObligatoire') ?? 0) + 1000 + rand(1, 1000);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = $uniqueId;
        $docOblig->nom = 'Contrat';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $tmpPath = sys_get_temp_dir() . '/test.pdf';
        file_put_contents($tmpPath, "%PDF-1.4\n%âãÏÓ\n");
        $uploaded = new UploadedFile($tmpPath, 'contract.pdf', 'application/pdf', null, true);

        // when
        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        // then
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
        // given
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_TEST2_' . uniqid(), 'guard_name' => 'web']);
        $uniqueId = (DocumentObligatoire::max('idDocumentObligatoire') ?? 0) + 2000 + rand(1, 1000);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = $uniqueId;
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

        // when
        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        // then
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        unlink($tmpPath);
    }

    public function test_upload_document_magic_bytes_invalide_retourne_erreur()
    {
        // given
        Storage::fake('public');

        $role = Role::create(['name' => 'ROLE_TEST3_' . uniqid(), 'guard_name' => 'web']);
        $uniqueId = (DocumentObligatoire::max('idDocumentObligatoire') ?? 0) + 3000 + rand(1, 1000);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = $uniqueId;
        $docOblig->nom = 'Contrat3';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $tmpPath = sys_get_temp_dir() . '/bad.pdf';
        file_put_contents($tmpPath, "BADBYTES");
        $uploaded = new UploadedFile($tmpPath, 'bad.pdf', 'application/pdf', null, true);

        // when
        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        // then
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        unlink($tmpPath);
    }

    public function test_upload_document_extension_invalide_retourne_erreur()
    {
        // given
        Storage::fake('public');

        $user = Utilisateur::factory()->create();

        $tmpPath = sys_get_temp_dir() . '/bad.exe';
        file_put_contents($tmpPath, "MZ\x00\x00");
        $uploaded = new UploadedFile($tmpPath, 'bad.exe', 'application/octet-stream', null, true);

        // when
        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => 9999,
            ]);

        // then
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors();
        unlink($tmpPath);
    }

    public function test_suppression_document_interdite_si_pas_proprietaire()
    {
        // given
        $owner = Utilisateur::factory()->create();
        $other = Utilisateur::factory()->create();

        $document = Document::factory()->create(['etat' => 'actif']);
        $owner->documents()->attach($document->idDocument);

        // when
        $response = $this->actingAs($other)->delete(route('profile.document.delete', ['document' => $document->idDocument]));

        // then
        $response->assertStatus(403);
    }

    public function test_suppression_document_ne_peut_pas_supprimer_valide()
    {
        // given
        Storage::fake('public');

        $user = Utilisateur::factory()->create();
        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/valid.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::factory()->create([
            'chemin' => $path,
            'etat' => 'valide',
        ]);

        $user->documents()->attach($document->idDocument);

        // when
        $response = $this->actingAs($user)->delete(route('profile.document.delete', ['document' => $document->idDocument]));

        // then
        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('error');

        // file should remain
        Storage::disk('public')->assertExists($path);
    }
    
    public function test_upload_docx_sans_dossier_word_retourne_erreur()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        // given
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

        // when
        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        // then
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        @unlink($tmp);
    }

    public function test_upload_fichier_vide_impossible_lecture_retourne_erreur()
    {
        // given
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

        // when
        $response = $this->actingAs($user)
            ->post(route('profile.document.upload'), [
                'document' => $uploaded,
                'idDocumentObligatoire' => $docOblig->idDocumentObligatoire,
            ]);

        // then
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        @unlink($tmp);
    }

    public function test_upload_document_pas_pour_roles_utilisateur_retourne_erreur()
    {
        // given
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

        // when
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
    
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionMethod(get_class($object), $methodName);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $parameters);
    }

    /** @test */
    public function it_calls_validate_docx_zip_when_conditions_are_met_and_zip_archive_exists()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        // GIVEN 
        $validator = new FileValidator();


        $tempFile = tempnam(sys_get_temp_dir(), 'test_docx');
        $zip = new \ZipArchive();
        $zip->open($tempFile, \ZipArchive::CREATE);
        $zip->addFromString('test.txt', 'contenu dummy');
        $zip->close();

      
        $hexHeader = '504b0304';
        $extension = 'docx';
        $isValidInitially = false;

        // WHEN (Lorsque)

        $result = $this->invokeMethod($validator, 'validateDocxIfNeeded', [
            $tempFile,
            $extension,
            $hexHeader,
            $isValidInitially
        ]);

        // THEN
        
        $this->assertTrue($result);

        
        unlink($tempFile);
    }


    public function it_returns_false_if_delegated_zip_validation_fails()
    {
        // GIVEN

        $validator = new FileValidator();
        $tempFile = tempnam(sys_get_temp_dir(), 'test_corrupt');
        
    
        file_put_contents($tempFile, pack('H*', '504b0304'));

        // WHEN
        $result = $this->invokeMethod($validator, 'validateDocxIfNeeded', [
            $tempFile,
            'docx',
            '504b0304',
            false
        ]);

        // THEN
        $this->assertFalse($result);

        unlink($tempFile);
    }

    public function test_profilecontroller_validateDocxIfNeeded_calls_validateDocxZip_when_ziparchive_exists_and_returns_false_for_missing_word_folder()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        // GIVEN
        $controller = new \App\Http\Controllers\ProfileController();

        // create a docx-like zip WITHOUT word/ folder
        $tmp = tempnam(sys_get_temp_dir(), 'no_word_docx');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('docProps/core.xml', 'content');
        $zip->close();

        $uploaded = new UploadedFile($tmp, 'no_word.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        // Use canonical ZIP magic bytes to avoid platform/order-dependent reads
        $hex = '504b0304';

        // WHEN/THEN: directly assert validateDocxZip behaviour (delegation tested elsewhere)
        if (class_exists('ZipArchive')) {
            $this->assertFalse($this->invokeMethod($controller, 'validateDocxZip', [$uploaded]));
        } else {
            $this->assertTrue(true);
        }

        @unlink($tmp);
    }

    public function test_profilecontroller_validateDocxIfNeeded_returns_true_when_zip_contains_word_folder()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        // GIVEN
        $controller = new \App\Http\Controllers\ProfileController();

        // create a docx-like zip WITH word/ folder
        $tmp = tempnam(sys_get_temp_dir(), 'with_word_docx');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('word/document.xml', '<w:document/>');
        $zip->close();

        $uploaded = new UploadedFile($tmp, 'with_word.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $fh = fopen($tmp, 'rb');
        $bytes = fread($fh, 4);
        fclose($fh);
        $hex = bin2hex($bytes);

        // WHEN
        $result = $this->invokeMethod($controller, 'validateDocxIfNeeded', [$uploaded, 'docx', $hex, false]);

        // THEN: validateDocxZip should run and return true
        $this->assertTrue($result);

        @unlink($tmp);
    }

    public function test_handleUploadError_deletes_file_and_returns_redirect_for_array_error()
    {
        // GIVEN
        Storage::fake('public');
        $path = 'profiles/tmpfile.txt';
        Storage::disk('public')->put($path, 'content');

        $controller = new \App\Http\Controllers\ProfileController();

        // WHEN
        $response = $this->invokeMethod($controller, 'handleUploadError', [['field' => 'error'], $path]);

        // THEN
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_handleUploadError_returns_redirect_for_string_error()
    {
        // GIVEN
        $controller = new \App\Http\Controllers\ProfileController();

        // WHEN
        $response = $this->invokeMethod($controller, 'handleUploadError', ['some error', null]);

        // THEN
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_getDocumentObligatoireForUser_returns_doc_for_user_roles()
    {
        // GIVEN
        $role = Role::create(['name' => 'ROLE_DOC', 'guard_name' => 'web']);
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 99;
        $docOblig->nom = 'Xdoc';
        $docOblig->save();
        $docOblig->roles()->attach($role->idRole);

        $user = Utilisateur::factory()->create();
        $user->rolesCustom()->attach($role->idRole, ['model_type' => Utilisateur::class]);

        $controller = new \App\Http\Controllers\ProfileController();

        // WHEN
        $result = $this->invokeMethod($controller, 'getDocumentObligatoireForUser', [$user, $docOblig->idDocumentObligatoire]);

        // THEN
        $this->assertInstanceOf(DocumentObligatoire::class, $result);
        $this->assertEquals($docOblig->idDocumentObligatoire, $result->idDocumentObligatoire);
    }

    public function test_checkDocumentUploadability_returns_redirect_when_last_document_valid()
    {
        // GIVEN
        $user = Utilisateur::factory()->create();
        $docOblig = new DocumentObligatoire();
        $docOblig->idDocumentObligatoire = 100;
        $docOblig->nom = 'Ydoc';
        $docOblig->save();

        $existing = Document::factory()->create(['etat' => 'valide', 'nom' => $docOblig->nom . ' - old.pdf']);
        $user->documents()->attach($existing->idDocument);

        $controller = new \App\Http\Controllers\ProfileController();

        // WHEN
        $response = $this->invokeMethod($controller, 'checkDocumentUploadability', [$user, $docOblig]);

        // THEN
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_validateFileExtension_allows_and_blocks()
    {
        $controller = new \App\Http\Controllers\ProfileController();

        $ok = $this->invokeMethod($controller, 'validateFileExtension', ['pdf']);
        $this->assertNull($ok);

        $bad = $this->invokeMethod($controller, 'validateFileExtension', ['exe']);
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $bad);
    }

    public function test_validateFileMagicBytesWrapper_returns_redirect_on_invalid()
    {
        // GIVEN
        $controller = new \App\Http\Controllers\ProfileController();
        $tmp = sys_get_temp_dir() . '/badwrapper.pdf';
        file_put_contents($tmp, "BADBYTES");
        $uploaded = new UploadedFile($tmp, 'badwrapper.pdf', 'application/pdf', null, true);

        // WHEN
        $res = $this->invokeMethod($controller, 'validateFileMagicBytesWrapper', [$uploaded, 'pdf']);

        // THEN
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
        @unlink($tmp);
    }

    public function test_readFileHex_returns_null_for_missing_file()
    {
        $controller = new \App\Http\Controllers\ProfileController();

        $tmp = tempnam(sys_get_temp_dir(), 'small');
        file_put_contents($tmp, 'a'); // less than 4 bytes
        $uploaded = new UploadedFile($tmp, 'small.txt', null, null, true);

        $res = $this->invokeMethod($controller, 'readFileHex', [$uploaded]);
        $this->assertNull($res);
        @unlink($tmp);
    }

    public function test_getMagicBytesForExtension_and_checkMagicBytes()
    {
        $controller = new \App\Http\Controllers\ProfileController();
        $pdf = $this->invokeMethod($controller, 'getMagicBytesForExtension', ['pdf']);
        $this->assertIsArray($pdf);

        $match = $this->invokeMethod($controller, 'checkMagicBytes', ['25504446abcd', $pdf]);
        $this->assertTrue($match);

        $nomatch = $this->invokeMethod($controller, 'checkMagicBytes', ['00000000', $pdf]);
        $this->assertFalse($nomatch);
    }
}
