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
                    'content'  => file_get_contents(Storage::disk('public')->path($chemin)),
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

        return response($manualFile['content'], 200)
            ->header('Content-Type', ($manualFile['ext'] === 'pdf') ? 'application/pdf' : 'application/vnd.ms-word')
            ->header('Content-Disposition', 'attachment; filename="' . $manualFile['filename'] . '"');
    }

    public function generateFactureToWord(Facture $facture)
    {
        $factureCalculator = app()->make('App\Services\FactureCalculator');
        $montants          = $factureCalculator->calculerMontantFacture((string) $facture->idFacture);

        // Protect against RedirectResponse returned by the calculator
        if ($montants instanceof RedirectResponse) {
            $montants = [
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
        } else {
            if (! $facture->previsionnel) {
                $montants['regularisation'] = $factureCalculator->calculerRegularisation($facture->idFacture);
            }
        }

        $parent    = $facture->utilisateur; // relation property
        $nbEnfants = $montants['nbEnfants'] ?? 0;

        $templatePath = storage_path('app/templates/facture_template.docx');

        if (! file_exists($templatePath)) {
            abort(500, "Le modèle Word est introuvable à : " . $templatePath);
        }

        // 3. Initialisation de PhpWord TemplateProcessor et remplissage des variables
        $outputDir = storage_path('app/public/factures/');
        if (! file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $docxFileName = 'facture-' . $facture->idFacture . '.docx';
        $docxPath     = $outputDir . $docxFileName;

        try {
            $templateProcessor = new TemplateProcessor($templatePath);

            $templateProcessor->setValue('idFacture', $facture->idFacture);
            $templateProcessor->setValue('dateFacture', $facture->dateC->format('d/m/Y'));

            $templateProcessor->setValue('nom', $parent ? $parent->nom : '');
            $templateProcessor->setValue('nbEnfants', $nbEnfants);

            $templateProcessor->setValue('montantCotisation', number_format($montants['montantcotisation'] ?? 0, 2, ',', ' '));
            $templateProcessor->setValue('montantParticipation', number_format($montants['montantparticipation'] ?? 0, 2, ',', ' '));
            $templateProcessor->setValue('montantParticiparionSeaska', number_format($montants['montantparticipationSeaska'] ?? 0, 2, ',', ' '));
            $templateProcessor->setValue('montantgarderie', number_format($montants['montangarderie'] ?? 0, 2, ',', ' '));

            //
            // Work with numeric values for calculations, format only for the template
            $valeurPrevisionnelleNumeric = floatval($montants['totalPrevisionnel'] ?? 0);
            if ($facture->previsionnel) {
                $templateProcessor->cloneRow('montantreg', 0);
                $montantReg = 0;
            } else {
                $montantReg                   = $factureCalculator->calculerRegularisation($facture->idFacture);
                $valeurPrevisionnelleNumeric += floatval($montantReg ?? 0);
                $templateProcessor->setValue('montantreg', number_format($montantReg ?? 0, 2, ',', ' '));
            }

            // Format values for insertion into the template
            $valeurPrevisionnelle = number_format($valeurPrevisionnelleNumeric, 2, ',', ' ');

            // récupération de la parité pour la famille
            $parite = 0;

            $idFamille         = $facture->idFamille;
            $familleSpecifique = $parent->familles()->where('famille.idFamille', $idFamille)->first();
            if ($familleSpecifique && isset($familleSpecifique->pivot->parite)) {
                $parite = $familleSpecifique->pivot->parite;
            }

            // Apply parite (percentage) to compute final total for this parent
            $pariteNumeric   = is_numeric($parite) ? floatval($parite) : 0.0;
            $totalTtcNumeric = $valeurPrevisionnelleNumeric * (($pariteNumeric / 100));

            $templateProcessor->setValue('pariter', $pariteNumeric);
            $templateProcessor->setValue('totalPrevisionnel', $valeurPrevisionnelle);
            $templateProcessor->setValue('total', number_format($totalTtcNumeric, 2, ',', ' '));

            $templateProcessor->saveAs($docxPath);

            // convert to PDF
            $factureConversionService = app()->make('App\Services\FactureConversionService');
            $factureConversionService->convertFactureToPdf($facture);

        } catch (\Throwable $e) {
            // If TemplateProcessor fails for any reason, copy the raw template as a fallback
            dd($e->getMessage());

        }

    }

}
