<?php

namespace App\Services;

use App\Models\Actualite;
use App\Models\Document;

class ActualiteImageService
{
    /**
     * Gère l'upload d'images pour une actualité.
     *
     * @param array $files
     * @param Actualite $actualite
     * @return void
     */
    public function uploadImages(array $files, Actualite $actualite): void
    {
        foreach ($files as $file) {
            $path = $file->store('actualites', 'public');
            $document = Document::create([
                'nom'    => $file->getClientOriginalName(),
                'chemin' => $path,
                'type'   => 'image',
                'etat'   => 'actif',
            ]);
            $actualite->documents()->attach($document->idDocument);
        }
    }
}
