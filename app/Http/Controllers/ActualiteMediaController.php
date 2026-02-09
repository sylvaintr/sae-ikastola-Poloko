<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActualiteMediaController extends Controller
{
    private const ZIP_PREFIX_IMAGES = 'image_';

    /**
     * Télécharge toutes les images d'une actualité sous forme de ZIP.
     * Nom du ZIP: image_TitreActualité.zip
     */
    public function downloadImagesZip(Actualite $actualite): BinaryFileResponse
    {
        $images = $this->getActualiteImages($actualite);
        if ($images->isEmpty()) {
            abort(404, 'Aucune image à télécharger.');
        }

        $zipFilename = $this->buildZipFilename($actualite);
        $tmpPath = $this->createTempZipPath();

        $zip = $this->openZip($tmpPath);
        $this->addImagesToZip($zip, $images);
        $zip->close();

        return response()
            ->download($tmpPath, $zipFilename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Sert un document (image) d'une actualité via une route Laravel.
     * Permet d'afficher les images même si le lien `public/storage` n'existe pas.
     */
    public function showDocument(Actualite $actualite, Document $document)
    {
        if (! $this->documentIsAttachedToActualite($actualite, $document)) {
            abort(404);
        }

        $path = $this->resolvePublicPathFromDocument($document);
        $absolutePath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            // Laisse le navigateur afficher (pas forcer le download)
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    private function getActualiteImages(Actualite $actualite)
    {
        $actualite->loadMissing('documents');

        return $actualite->documents->where('type', 'image')->values();
    }

    private function buildZipFilename(Actualite $actualite): string
    {
        $titre = $this->resolveActualiteTitreForZip($actualite);
        $base = $this->sanitizeFilename(self::ZIP_PREFIX_IMAGES . $titre);

        return $base . '.zip';
    }

    private function resolveActualiteTitreForZip(Actualite $actualite): string
    {
        if (!empty($actualite->titrefr)) {
            return (string) $actualite->titrefr;
        }

        if (!empty($actualite->titreeus)) {
            return (string) $actualite->titreeus;
        }

        return 'actualite_' . $actualite->idActualite;
    }

    private function createTempZipPath(): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . uniqid('actu_images_', true)
            . '.zip';
    }

    private function openZip(string $tmpPath): \ZipArchive
    {
        $zip = new \ZipArchive();
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, "Impossible de créer l'archive ZIP.");
        }

        return $zip;
    }

    private function addImagesToZip(\ZipArchive $zip, $images): void
    {
        $usedNames = [];
        $i = 1;

        foreach ($images as $doc) {
            $pathInDisk = $this->resolveExistingPublicDocumentPathOrNull($doc);
            if ($pathInDisk === null) {
                continue;
            }

            $absolutePath = Storage::disk('public')->path($pathInDisk);
            $ext = pathinfo($pathInDisk, PATHINFO_EXTENSION);

            $entryName = $this->buildZipEntryName($doc, $i, $ext, $usedNames);
            $zip->addFile($absolutePath, $entryName);
            $i++;
        }
    }

    private function buildZipEntryName(Document $doc, int $i, string $ext, array &$usedNames): string
    {
        $name = $doc->nom ?: $this->defaultImageName($i, $ext);
        $name = $this->sanitizeFilename((string) $name);

        $nameWithExt = $this->ensureExtension($name, $ext);

        $candidate = $nameWithExt;
        $suffix = 2;
        while (isset($usedNames[strtolower($candidate)])) {
            $candidate = pathinfo($nameWithExt, PATHINFO_FILENAME) . '_' . $suffix . ($ext ? ('.' . $ext) : '');
            $suffix++;
        }

        $usedNames[strtolower($candidate)] = true;
        return $candidate;
    }

    private function defaultImageName(int $i, string $ext): string
    {
        if ($ext === '') {
            return 'image_' . $i;
        }

        return 'image_' . $i . '.' . $ext;
    }

    private function ensureExtension(string $name, string $ext): string
    {
        if ($ext === '') {
            return $name;
        }

        $lower = strtolower($name);
        $suffix = '.' . strtolower($ext);
        if (substr($lower, -strlen($suffix)) === $suffix) {
            return $name;
        }

        return $name . '.' . $ext;
    }

    private function resolveExistingPublicDocumentPathOrNull(Document $document): ?string
    {
        $path = $document->chemin;
        if (!is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    private function documentIsAttachedToActualite(Actualite $actualite, Document $document): bool
    {
        return $actualite->documents()
            ->wherePivot('idDocument', $document->getKey())
            ->exists();
    }

    private function resolvePublicPathFromDocument(Document $document): string
    {
        $path = $document->chemin;
        if (!is_string($path) || $path === '' || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return $path;
    }

    /**
     * Nettoie une chaîne pour un nom de fichier (ZIP et fichiers internes).
     */
    private function sanitizeFilename(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?? $name;
        $name = preg_replace('/_+/', '_', $name) ?? $name;
        $name = trim($name, '_');

        return $name !== '' ? $name : 'fichier';
    }
}

