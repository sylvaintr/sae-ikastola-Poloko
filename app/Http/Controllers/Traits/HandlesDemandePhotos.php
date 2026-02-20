<?php

namespace App\Http\Controllers\Traits;

use App\Models\Tache;
use App\Models\Document;

trait HandlesDemandePhotos
{
    /**
     * Sauvegarde les photos liées à la demande (si présentes).
     */
    protected function storePhotos(Tache $demande, array $files): void
    {
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $path = $file->store('demandes', 'public');

            Document::create([
                'idTache' => $demande->idTache,
                'nom' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'chemin' => $path,
                'type' => substr($file->extension(), 0, 5),
                'etat' => 'actif',
            ]);
        }
    }
}
