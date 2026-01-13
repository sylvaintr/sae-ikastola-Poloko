<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Utilisateur;
use App\Models\Document;

class ProfileControllerDeleteDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppression_document_refuse_si_non_autorise()
    {
        // given
        Storage::fake('public');

        $user = Utilisateur::factory()->create();

        $document = Document::create([
            'nom' => 'file.pdf',
            'chemin' => 'profiles/' . $user->idUtilisateur . '/obligatoires/file.pdf',
            'type' => 'doc',
            'etat' => 'valide',
        ]);

        $user->documents()->attach($document->idDocument);

        // when
        $response = $this->actingAs($user)->delete(route('profile.document.delete', ['document' => $document->idDocument]));

        // then
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_suppression_document_succes_supprime_fichier_et_detache()
    {
        // given
        Storage::fake('public');

        $user = Utilisateur::factory()->create();

        $path = 'profiles/' . $user->idUtilisateur . '/obligatoires/file.pdf';
        Storage::disk('public')->put($path, 'content');

        $document = Document::create([
            'nom' => 'file.pdf',
            'chemin' => $path,
            'type' => 'doc',
            'etat' => 'actif',
        ]);

        $user->documents()->attach($document->idDocument);

        // when
        $response = $this->actingAs($user)->delete(route('profile.document.delete', ['document' => $document->idDocument]));

        // then
        $response->assertRedirect();
        $response->assertSessionHas('status');
        Storage::disk('public')->assertMissing($path);
    }
}
