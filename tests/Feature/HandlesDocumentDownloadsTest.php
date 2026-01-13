<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Utilisateur;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class HandlesDocumentDownloadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_downloadDocumentWithFormattedName_uses_extension_from_nom_when_path_has_no_extension()
    {
        $this->withoutMiddleware();
        Storage::fake('public');

        $user = Utilisateur::factory()->create(['nom' => 'Dupont', 'prenom' => 'Jean']);

        // chemin has no extension to trigger the empty($extension) branch
        $chemin = 'profiles/' . $user->idUtilisateur . '/obligatoires/file_without_ext';
        Storage::disk('public')->put($chemin, 'content');

        // nom contains an extension part that should be used
        $document = Document::factory()->create(['chemin' => $chemin, 'nom' => 'Attestation - original.pdf']);

        // ensure pivot exists
        DB::table('contenir')->insert(['idUtilisateur' => $user->idUtilisateur, 'idDocument' => $document->idDocument]);

        // Create an anonymous invoker exposing the protected trait method
        $invoker = new class {
            use \App\Http\Controllers\Traits\HandlesDocumentDownloads;
            public function call($u, $d)
            {
                return $this->downloadDocumentWithFormattedName($u, $d);
            }
        };

        $response = $invoker->call($user, $document);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response);

        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('.pdf', $disposition);

        // formatted name should include user name and document name part
        $this->assertStringContainsString('Dupont_Jean_Attestation.pdf', $disposition);
    }

    public function test_downloadDocumentWithFormattedName_defaults_to_pdf_when_nom_has_no_extension()
    {
        $this->withoutMiddleware();
        Storage::fake('public');

        $user = Utilisateur::factory()->create(['nom' => 'Dupont', 'prenom' => 'Jean']);

        // chemin has no extension to trigger the empty($extension) branch
        $chemin = 'profiles/' . $user->idUtilisateur . '/obligatoires/file_without_ext2';
        Storage::disk('public')->put($chemin, 'content');

        // nom has no dot, so count($extensionParts) <= 1 and default 'pdf' should be used
        $document = Document::factory()->create(['chemin' => $chemin, 'nom' => 'Attestation - original']);

        // ensure pivot exists
        DB::table('contenir')->insert(['idUtilisateur' => $user->idUtilisateur, 'idDocument' => $document->idDocument]);

        $invoker = new class {
            use \App\Http\Controllers\Traits\HandlesDocumentDownloads;
            public function call($u, $d)
            {
                return $this->downloadDocumentWithFormattedName($u, $d);
            }
        };

        $response = $invoker->call($user, $document);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response);

        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('.pdf', $disposition);
        $this->assertStringContainsString('Dupont_Jean_Attestation.pdf', $disposition);
    }
}
