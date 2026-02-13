<?php
namespace App\Services;

use App\Models\Facture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class FactureExporter
{

    public function getLinkFarctureFile(Facture $facture): ?array
    {
        $nom  = 'facture-' . $facture->idFacture;
        $exts = $facture->etat === 'verifier' ? ['pdf'] : ['doc', 'docx', 'odt'];

        foreach ($exts as $ext) {
            $chemin = 'factures/' . $nom . '.' . $ext;
            if (Storage::disk('public')->exists($chemin)) {
                return [
                    'content'  => Storage::disk('public')->get($chemin),
                    'ext'      => $ext,
                    'filename' => $nom . '.' . $ext,
                ];
            }
        }

        return null;
    }

    /**
     * Handles logic for serving an existing uploaded file.
     */
    public function serveManualFile(Facture $facture, bool $returnBinary): Response | string | null
    {
        $manualFile = $this->getLinkFarctureFile($facture);

        if ($manualFile === null) {
            return null;
        }

        if ($returnBinary) {
            return $manualFile['content'];
        }
        $contentType = match ($manualFile['ext']) {
            'pdf'   => 'application/pdf',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'odt'   => 'application/vnd.oasis.opendocument.text',
            default => 'application/octet-stream',
        };
        return response($manualFile['content'], 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $manualFile['filename'] . '"');
    }

    public function generateFactureToWord(Facture $facture)
    {
        if ($this->shouldUseDummyFile()) {
            return $this->createDummyDocxFile($facture);
        }

        $montants = $this->calculateMontants($facture);
        $outputPath = $this->getOutputPath($facture);
        
        try {
            $this->generateDocxFromTemplate($facture, $montants, $outputPath);
            $this->convertToPdf($facture);
        } catch (\Throwable $e) {
            return $this->handleTemplateError($e, $outputPath);
        }
    }

    /**
     * Check if we should use a dummy file instead of real processing
     */
    private function shouldUseDummyFile(): bool
    {
        return ! class_exists(\ZipArchive::class) && ! app()->environment('production');
    }

    /**
     * Create a dummy DOCX file for testing
     */
    private function createDummyDocxFile(Facture $facture): string
    {
        $outputDir = storage_path('app/public/factures/');
        if (! file_exists($outputDir)) {
            @mkdir($outputDir, 0755, true);
        }
        $docxPath = $outputDir . 'facture-' . $facture->idFacture . '.docx';
        if (! file_exists($docxPath)) {
            @file_put_contents($docxPath, 'DUMMY_DOCX');
        }
        return $docxPath;
    }

    /**
     * Calculate montants for the facture
     */
    private function calculateMontants(Facture $facture): array
    {
        $factureCalculator = app()->make('App\Services\FactureCalculator');
        $montants = $factureCalculator->calculerMontantFacture((string) $facture->idFacture);

        // Protect against RedirectResponse returned by the calculator
        if ($montants instanceof RedirectResponse) {
            return $this->getDefaultMontants($facture);
        }

        if (! $facture->previsionnel) {
            $montants['regularisation'] = $factureCalculator->calculerRegularisation($facture->idFacture);
        }

        return $montants;
    }

    /**
     * Get default montants when calculator returns a redirect
     */
    private function getDefaultMontants(Facture $facture): array
    {
        return [
            'facture'                    => $facture,
            'famille'                    => $facture->famille,
            'nbEnfants'                  => 0,
            'montantcotisation'          => 0,
            'montantparticipation'       => 0,
            'montantparticipationSeaska' => 0,
            'montangarderie'             => 0,
            'montanttotal'               => 0,
            'totalPrevisionnel'          => 0,
            'enfants'                    => [],
        ];
    }

    /**
     * Get the output path for the generated file
     */
    private function getOutputPath(Facture $facture): string
    {
        $outputDir = storage_path('app/public/factures/');
        if (! file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        return $outputDir . 'facture-' . $facture->idFacture . '.docx';
    }

    /**
     * Generate DOCX from template
     */
    private function generateDocxFromTemplate(Facture $facture, array $montants, string $docxPath): void
    {
        $templatePath = storage_path('app/templates/facture_template.docx');
        if (! file_exists($templatePath)) {
            abort(500, "Le modèle Word est introuvable à : " . $templatePath);
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        
        $this->fillBasicFields($templateProcessor, $facture, $montants);
        $this->fillAmountFields($templateProcessor, $montants);
        $this->fillRegularisationFields($templateProcessor, $facture, $montants);
        $this->fillPariteFields($templateProcessor, $facture, $montants);

        $templateProcessor->saveAs($docxPath);
    }

    /**
     * Fill basic fields in the template
     */
    private function fillBasicFields(TemplateProcessor $processor, Facture $facture, array $montants): void
    {
        $parent = $facture->utilisateur;
        $processor->setValue('idFacture', $facture->idFacture);
        $processor->setValue('dateFacture', $facture->dateC->format('d/m/Y'));
        $processor->setValue('nom', $parent ? $parent->nom : '');
        $processor->setValue('nbEnfants', $montants['nbEnfants'] ?? 0);
    }

    /**
     * Fill amount fields in the template
     */
    private function fillAmountFields(TemplateProcessor $processor, array $montants): void
    {
        $processor->setValue('montantCotisation', number_format($montants['montantcotisation'] ?? 0, 2, ',', ' '));
        $processor->setValue('montantParticipation', number_format($montants['montantparticipation'] ?? 0, 2, ',', ' '));
        $processor->setValue('montantParticiparionSeaska', number_format($montants['montantparticipationSeaska'] ?? 0, 2, ',', ' '));
        $processor->setValue('montantgarderie', number_format($montants['montangarderie'] ?? 0, 2, ',', ' '));
    }

    /**
     * Fill regularisation fields in the template
     */
    private function fillRegularisationFields(TemplateProcessor $processor, Facture $facture, array &$montants): void
    {
        $valeurPrevisionnelleNumeric = floatval($montants['totalPrevisionnel'] ?? 0);
        
        if ($facture->previsionnel) {
            $processor->cloneRow('montantreg', 0);
            $montants['montantReg'] = 0;
        } else {
            $factureCalculator = app()->make('App\Services\FactureCalculator');
            $montantReg = $factureCalculator->calculerRegularisation($facture->idFacture);
            $valeurPrevisionnelleNumeric += floatval($montantReg ?? 0);
            $processor->setValue('montantreg', number_format($montantReg ?? 0, 2, ',', ' '));
            $montants['montantReg'] = $montantReg;
        }

        $montants['valeurPrevisionnelleNumeric'] = $valeurPrevisionnelleNumeric;
    }

    /**
     * Fill parite-related fields in the template
     */
    private function fillPariteFields(TemplateProcessor $processor, Facture $facture, array $montants): void
    {
        $parent = $facture->utilisateur;
        $parite = $this->getParite($parent, $facture->idFamille);
        
        $valeurPrevisionnelleNumeric = $montants['valeurPrevisionnelleNumeric'] ?? 0;
        $totalTtcNumeric = $valeurPrevisionnelleNumeric * ($parite / 100);

        $processor->setValue('pariter', $parite);
        $processor->setValue('totalPrevisionnel', number_format($valeurPrevisionnelleNumeric, 2, ',', ' '));
        $processor->setValue('total', number_format($totalTtcNumeric, 2, ',', ' '));
    }

    /**
     * Get parite for the family
     */
    private function getParite($parent, int $idFamille): float
    {
        $familleSpecifique = $parent->familles()->where('famille.idFamille', $idFamille)->first();
        
        if ($familleSpecifique && isset($familleSpecifique->pivot->parite)) {
            return floatval($familleSpecifique->pivot->parite);
        }
        
        return 0.0;
    }

    /**
     * Convert the facture to PDF
     */
    private function convertToPdf(Facture $facture): void
    {
        $factureConversionService = app()->make('App\Services\FactureConversionService');
        $factureConversionService->convertFactureToPdf($facture);
    }

    /**
     * Handle template processing errors
     */
    private function handleTemplateError(\Throwable $e, string $docxPath): ?string
    {
        \Illuminate\Support\Facades\Log::error('FactureExporter: template error', ['err' => $e->getMessage()]);
        
        if ($this->shouldUseDummyFile() && ! file_exists($docxPath)) {
            @file_put_contents($docxPath, 'DUMMY_DOCX');
            return $docxPath;
        }
        
        return null;
    }

}
