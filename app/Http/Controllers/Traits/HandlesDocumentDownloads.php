<?php
namespace App\Http\Controllers\Traits;

use App\Models\Document;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait HandlesDocumentDownloads
{
    private const REGEX_CLEAN_FILENAME = '/[^a-zA-Z0-9]/';
    /**
     * Méthode de téléchargement d'un document obligatoire avec un nom de fichier formaté.
     * Format du nom : Nom_Prenom_NomDocumentObligatoire.extension
     *
     * @param Utilisateur $user     L'utilisateur propriétaire du document
     * @param Document    $document Le document à télécharger
     *
     * @return BinaryFileResponse Réponse de téléchargement du fichier avec le nom formaté
     * @throws \Illuminate\Auth\Access\AuthorizationException Si l'utilisateur n'est pas autorisé à télécharger le document
     * @throws \Illuminate\Http\Exceptions\FileNotFoundException Si le fichier du document n'existe pas
     */
    protected function downloadDocumentWithFormattedName(Utilisateur $user, Document $document): BinaryFileResponse
    {
        // Vérifier que le document appartient à l'utilisateur
        if (! $user->documents()->where('document.idDocument', $document->idDocument)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Vérifier que le fichier existe
        if (! Storage::disk('public')->exists($document->chemin)) {
            abort(404, 'File not found.');
        }

        // Le nom du document est formaté comme "NomDocumentObligatoire - nom_fichier_original"
        $nomParts               = explode(' - ', $document->nom, 2);
        $nomDocumentObligatoire = $nomParts[0];

        // Récupérer l'extension du fichier original
        $extension = pathinfo($document->chemin, PATHINFO_EXTENSION);
        if (empty($extension)) {
            // Si pas d'extension dans le chemin, essayer de la récupérer depuis le nom du document
            $extensionParts = explode('.', $document->nom);
            if (count($extensionParts) > 1) {
                $extension = strtolower(end($extensionParts));
            } else {
                $extension = 'pdf'; // Par défaut
            }
        }

        // Générer le nom de fichier : Nom_Prenom_NomDocumentObligatoire.extension
        $nomUtilisateur    = $user->nom ?? '';
        $prenomUtilisateur = $user->prenom ?? '';

        // Nettoyer les noms (remplacer les caractères spéciaux par des underscores)
        $nomUtilisateur         = preg_replace(self::REGEX_CLEAN_FILENAME, '_', $nomUtilisateur);
        $prenomUtilisateur      = preg_replace(self::REGEX_CLEAN_FILENAME, '_', $prenomUtilisateur);
        $nomDocumentObligatoire = preg_replace(self::REGEX_CLEAN_FILENAME, '_', $nomDocumentObligatoire);

        // Construire le nom de fichier
        $fileName = trim($nomUtilisateur . '_' . $prenomUtilisateur . '_' . $nomDocumentObligatoire);
        $fileName = preg_replace('/_+/', '_', $fileName); // Remplacer les underscores multiples par un seul
        $fileName = trim($fileName, '_');                 // Enlever les underscores en début/fin
        $fileName .= '.' . $extension;

        $filePath = Storage::disk('public')->path($document->chemin);

        return Response::download($filePath, $fileName);
    }
}
