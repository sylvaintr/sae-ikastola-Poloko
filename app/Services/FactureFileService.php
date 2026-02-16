<?php
namespace App\Services;

use App\Models\Facture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FactureFileService
{
    private $factureConversionService;

    public function __construct(FactureConversionService $factureConversionService)
    {
        $this->factureConversionService = $factureConversionService;
    }

    /**
     * Vérifie les magic bytes du fichier uploadé pour s'assurer du type.
     */
    public function isValidFileMagic($file): bool
    {
        $fh    = fopen($file->getRealPath(), 'rb');
        $bytes = fread($fh, 8);
        fclose($fh);

        $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"; // .doc (OLE)
        $zipHeader = "\x50\x4B\x03\x04";                 // .docx / .odt (ZIP)

        return strpos($bytes, $oleHeader) === 0 || strpos($bytes, $zipHeader) === 0;
    }

    /**
     * Supprime les fichiers existants pour une facture donnée.
     */
    public function deleteExistingFactureFiles(string $idFacture): void
    {
        $extensionsPossibles = ['doc', 'docx', 'odt'];
        foreach ($extensionsPossibles as $ext) {
            $ancienChemin = 'factures/facture-' . $idFacture . '.' . $ext;
            if (Storage::disk('public')->exists($ancienChemin)) {
                Storage::disk('public')->delete($ancienChemin);
            }
        }
    }

    /**
     * Stocke le fichier uploadé et lance la conversion en PDF.
     * Retourne false en cas d'échec, ou le nom de fichier stocké en cas de succès.
     */
    public function storeUploadedFacture($file, Facture $facture): bool | string
    {
        $extensionsPossibles = ['doc', 'docx', 'odt'];
        $extension           = strtolower($file->getClientOriginalExtension() ?? $file->extension());
        if (! in_array($extension, $extensionsPossibles, true)) {
            $extension = 'docx';
        }

        $filename = 'facture-' . $facture->idFacture . '.' . $extension;
        $stored   = $file->storeAs('public/factures', $filename);
        if ($stored === false) {
            return false;
        }

        Storage::disk('public')->setVisibility('factures/' . $filename, 'public');

        $this->factureConversionService->convertirWordToPdf(
            Storage::disk('public')->path('factures/' . $filename),
            Storage::disk('public')->path('factures/facture-' . $facture->idFacture . '.pdf')
        );

        return $filename;
    }

    /**
     * Process uploaded facture file from a Request: validation, delete old, store new.
     * Returns a RedirectResponse on error, or null on success/no-upload.
     */
    public function processUploadedFile(Request $request, Facture $facture): ?RedirectResponse
    {
        $response = null;

        if ($request->hasFile('facture')) {
            $file = $request->file('facture');

            if (! $this->isValidFileMagic($file)) {
                $response = redirect()->route('admin.facture.index')->with('error', 'facture.invalidfile');
            } else {
                $this->deleteExistingFactureFiles($facture->idFacture);

                try {
                    $stored = $this->storeUploadedFacture($file, $facture);
                    if ($stored === false) {
                        $response = redirect()->route('admin.facture.index')->with('error', 'facture.uploadfail');
                    }
                } catch (\Throwable $e) {
                    Log::error('Erreur lors de l\'upload facture', ['err' => $e->getMessage()]);
                    $response = redirect()->route('admin.facture.index')->with('error', 'facture.uploadfail');
                }
            }
        }

        return $response;
    }
}
