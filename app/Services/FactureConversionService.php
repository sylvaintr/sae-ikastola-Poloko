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
     * Méthode pour convertir une facture de Word/ODT à PDF et supprimer le fichier source après conversion réussie.
     * @param Facture $facture La facture à convertir
     * @return bool true si la conversion a réussi et le fichier source a été supprimé, false sinon
     */
    public function convertFactureToPdfAndDeleteWord(Facture $facture): bool
    {
        return $this->convertFactureToPdf($facture, true);
    }

    /**
     * Méthode pour convertir une facture de Word/ODT à PDF. Cette méthode recherche d'abord le fichier source de la facture dans les formats Word (doc, docx) ou ODT, puis utilise la méthode `convertirWordToPdf` pour effectuer la conversion. Si la conversion est réussie et que le paramètre `$deleteOriginal` est vrai, le fichier source est supprimé. La méthode retourne true si la conversion a réussi (indépendamment de la suppression du fichier source), ou false si aucun fichier source n'a été trouvé ou si la conversion a échoué.
     *
     * @param Facture $facture La facture à convertir
     * @param bool $deleteOriginal si true supprime le fichier source après conversion réussie
     * @return bool
     */
    public function convertFactureToPdf(Facture $facture, bool $deleteOriginal = false): bool
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

                $result  = $this->convertirWordToPdf($inputPath, $pdfCible);
                $success = $result['success'] ?? false;

                if ($success) {
                    if ($deleteOriginal) {
                        Storage::disk('public')->delete($ancienCheminRelatif);
                    }
                    Log::info('FactureConversionService: conversion réussie', ['id' => $facture->idFacture, 'cmd_output' => $result['output'] ?? []]);
                    return true;
                }

                Log::error('FactureConversionService: échec conversion', ['id' => $facture->idFacture, 'cmd_output' => $result['output'] ?? [], 'return' => $result['return'] ?? null]);
            }
        }

        Log::warning('FactureConversionService: aucun fichier source trouvé', ['id' => $facture->idFacture]);
        return false;
    }

    /**
     * Méthode pour exécuter la commande de conversion et retourne un tableau contenant success, output et return
     *
     * @param string $inputPath chemin complet du fichier source à convertir
     * @param string $outputPath chemin complet du fichier PDF cible à générer
     * @return array{success:bool, output:array, return:int} tableau contenant le résultat de la conversion :
     *      - success (bool) : indique si la conversion a réussi (true si le fichier PDF cible a été généré, false sinon)
     *      - output (array) : tableau des lignes de sortie de la commande d'exécution, utile pour le débogage
     *      - return (int) : code de retour de la commande d'exécution, où 0 indique généralement une exécution réussie, et tout autre valeur indique une erreur
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
