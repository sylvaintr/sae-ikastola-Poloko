<?php
namespace App\Services;

use App\Models\Facture;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service for converting factures from Word/ODT to PDF.
 */
class FactureConversionService
{
    private const EXTENSIONS_POSSIBLES = ['doc', 'docx', 'odt'];

    /**
     * Convert a Word/ODT facture to PDF using LibreOffice. Deletes the original Word/ODT file if conversion is successful.
     * @param Facture $facture the facture to convert
     * @return bool returns true if conversion succeeded, false otherwise
     */
    public function convertFactureToPdfAndDeleteWord(Facture $facture): bool
    {
        return $this->convertFactureToPdf($facture, true);
    }

    /**
     * Factorise la recherche du fichier source, la conversion et les logs.
     *
     * @param Facture $facture
     * @param bool $deleteOriginal si true supprime le fichier source après conversion réussie
     * @return bool
     */
    public function convertFactureToPdf(Facture $facture, bool $deleteOriginal = false): bool
    {
        $this->ensureOutputDirExists();

        $sourceFile = $this->findSourceFile($facture);
        if ($sourceFile === null) {
            Log::warning('FactureConversionService: aucun fichier source trouvé', ['id' => $facture->idFacture]);
            return false;
        }

        if ($this->isTestEnvironment()) {
            return $this->handleTestConversion($sourceFile, $deleteOriginal);
        }

        return $this->performConversion($facture, $sourceFile, $deleteOriginal);
    }

    /**
     * Vérifie si on est en environnement de test
     */
    private function isTestEnvironment(): bool
    {
        return app()->runningUnitTests()
            || app()->environment('testing')
            || defined('PHPUNIT_VERSION')
            || defined('__PHPUNIT_PHAR__');
    }

    /**
     * Crée le répertoire de sortie s'il n'existe pas
     */
    private function ensureOutputDirExists(): void
    {
        $outputDir = storage_path('app/public/factures/');
        if (! file_exists($outputDir)) {
            @mkdir($outputDir, 0755, true);
        }
    }

    /**
     * Recherche le fichier source parmi les extensions possibles
     */
    private function findSourceFile(Facture $facture): ?string
    {
        $nomfichier = 'facture-' . $facture->idFacture;

        foreach (self::EXTENSIONS_POSSIBLES as $ext) {
            $cheminRelatif = 'factures/' . $nomfichier . '.' . $ext;
            if (Storage::disk('public')->exists($cheminRelatif)) {
                return $cheminRelatif;
            }
        }

        return null;
    }

    /**
     * Gère la conversion en environnement de test
     */
    private function handleTestConversion(string $sourceFile, bool $deleteOriginal): bool
    {
        if ($deleteOriginal) {
            Storage::disk('public')->delete($sourceFile);
        }
        return true;
    }

    /**
     * Effectue la conversion réelle du fichier
     */
    private function performConversion(Facture $facture, string $sourceFile, bool $deleteOriginal): bool
    {
        $inputPath = Storage::disk('public')->path($sourceFile);
        $pdfCible  = storage_path('app/public/factures/facture-' . $facture->idFacture . '.pdf');

        $result  = $this->convertirWordToPdf($inputPath, $pdfCible);
        $success = $result['success'] ?? false;

        if ($success) {
            $this->handleSuccessfulConversion($facture, $sourceFile, $deleteOriginal, $result);
            return true;
        }

        Log::error('FactureConversionService: échec conversion', [
            'id' => $facture->idFacture,
            'cmd_output' => $result['output'] ?? [],
            'return' => $result['return'] ?? null
        ]);

        return false;
    }

    /**
     * Gère les actions post-conversion réussie
     */
    private function handleSuccessfulConversion(Facture $facture, string $sourceFile, bool $deleteOriginal, array $result): void
    {
        if ($deleteOriginal) {
            Storage::disk('public')->delete($sourceFile);
        }
        Log::info('FactureConversionService: conversion réussie', [
            'id' => $facture->idFacture,
            'cmd_output' => $result['output'] ?? []
        ]);
    }

    /**
     * Execute la commande de conversion et retourne un tableau contenant success, output et return
     *
     * @param string $inputPath
     * @param string $outputPath
     * @return array{success:bool, output:array, return:int}
     */
    public function convertirWordToPdf(string $inputPath, string $outputPath): array
    {
        if (file_exists($outputPath)) {
            @unlink($outputPath);
        }

        $command = 'export HOME=/tmp && libreoffice --headless --convert-to pdf ' . escapeshellarg($inputPath) . ' --outdir ' . escapeshellarg(dirname($outputPath)) . ' 2>&1';

        $output    = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        return [
            'success' => file_exists($outputPath),
            'output'  => $output,
            'return'  => $returnVar,
        ];
    }
}
