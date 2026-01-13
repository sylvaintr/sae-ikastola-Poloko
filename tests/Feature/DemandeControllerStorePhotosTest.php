<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Tache;
use App\Models\Document;

class DemandeControllerStorePhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_storePhotos_creates_documents_for_each_uploaded_file()
    {
        $this->withoutMiddleware();
        Storage::fake('public');

        $demande = Tache::factory()->create();

        $files = [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.png'),
            UploadedFile::fake()->image('three.jpeg'),
        ];

        $invoker = new class extends \App\Http\Controllers\DemandeController {
            public function exposeStorePhotos($demande, $files)
            {
                return $this->storePhotos($demande, $files);
            }
        };

        // call the protected method via the exposing wrapper
        $invoker->exposeStorePhotos($demande, $files);

        // assert documents were created for this demande
        $count = Document::where('idTache', $demande->idTache)->count();
        $this->assertEquals(count($files), $count, 'storePhotos should create a document per uploaded file');

        // assert files were stored
        $stored = Storage::disk('public')->allFiles('demandes');
        $this->assertCount(count($files), $stored);
    }
}
