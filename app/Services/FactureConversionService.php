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
    /**
     * Convert a Word/ODT facture to PDF using LibreOffice. Deletes the original Word/ODT file if conversion is successful.
     * @param Facture $facture the facture to convert
     * @return bool returns true if conversion succeeded, false otherwise
     */
    public function convertFactureToPdfAndDeleteWord(Facture $facture): bool
    {
        $nomfichier          = 'facture-' . $facture->idFacture;
        $extensionsPossibles = ['doc', 'docx', 'odt'];
        $outputDir           = storage_path('app/public/factures/');

        if (! file_exists($outputDir)) {
            @mkdir($outputDir, 0755, true);
        }

        foreach ($extensionsPossibles as $ext) {
            $ancienCheminRelatif = 'factures/' . $nomfichier . '.' . $ext;

            if (Storage::disk('public')->exists($ancienCheminRelatif)) {
                $inputPath = Storage::disk('public')->path($ancienCheminRelatif);
                $pdfCible  = $outputDir . $nomfichier . '.pdf';

                if (file_exists($pdfCible)) {
                    @unlink($pdfCible);
                }

                $command = 'export HOME=/tmp && libreoffice --headless --convert-to pdf ' . escapeshellarg($inputPath) . ' --outdir ' . escapeshellarg($outputDir) . ' 2>&1';

                $output    = [];
                $returnVar = 0;
                exec($command, $output, $returnVar);

                if (file_exists($pdfCible)) {
                    // supprimer l'ancien Word
                    Storage::disk('public')->delete($ancienCheminRelatif);

                    Log::info('FactureConversionService: conversion réussie', ['id' => $facture->idFacture, 'cmd_output' => $output]);
                    return true;
                }

                Log::error('FactureConversionService: échec conversion', ['id' => $facture->idFacture, 'cmd_output' => $output, 'return' => $returnVar]);
            }
        }

        Log::warning('FactureConversionService: aucun fichier source trouvé', ['id' => $facture->idFacture]);
        return false;
    }

    public function convertFactureToPdf(Facture $facture): bool
    {
                $nomfichier          = 'facture-' . $facture->idFacture;
        $extensionsPossibles = ['doc', 'docx', 'odt'];
        $outputDir           = storage_path('app/public/factures/');

        if (! file_exists($outputDir)) {
            @mkdir($outputDir, 0755, true);
        }

        foreach ($extensionsPossibles as $ext) {
            $ancienCheminRelatif = 'factures/' . $nomfichier . '.' . $ext;

            if (Storage::disk('public')->exists($ancienCheminRelatif)) {
                $inputPath = Storage::disk('public')->path($ancienCheminRelatif);
                $pdfCible  = $outputDir . $nomfichier . '.pdf';

                if (file_exists($pdfCible)) {
                    @unlink($pdfCible);
                }

                $command = 'export HOME=/tmp && libreoffice --headless --convert-to pdf ' . escapeshellarg($inputPath) . ' --outdir ' . escapeshellarg($outputDir) . ' 2>&1';

                $output    = [];
                $returnVar = 0;
                exec($command, $output, $returnVar);

                if (file_exists($pdfCible)) {
                    // supprimer l'ancien Word
                    
                    Log::info('FactureConversionService: conversion réussie', ['id' => $facture->idFacture, 'cmd_output' => $output]);
                    return true;
                }

                Log::error('FactureConversionService: échec conversion', ['id' => $facture->idFacture, 'cmd_output' => $output, 'return' => $returnVar]);
            }
        }

        Log::warning('FactureConversionService: aucun fichier source trouvé', ['id' => $facture->idFacture]);
        return false;
    }
}
