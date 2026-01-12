<?php

namespace App\Services;

use App\Models\Facture;
use Pelago\Emogrifier\CssInliner;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class FactureExporter
{




    public function loadManualFile(Facture $facture): ?array
    {
        $nom = 'facture-' . $facture->idFacture;
        $exts = $facture->etat === 'manuel' ? ['doc', 'docx', 'odt'] : ['pdf'];

        foreach ($exts as $ext) {
            $chemin = 'factures/' . $nom . '.' . $ext;
            if (Storage::disk('public')->exists($chemin)) {
                return [
                    'content' => file_get_contents(Storage::disk('public')->path($chemin)),
                    'ext' => $ext,
                    'filename' => $nom . '.' . $ext,
                ];
            }
        }

        return null;
    }

    public function renderHtml(array $data): string
    {
        $html = view('facture.template.facture-html', $data)->render();
        return CssInliner::fromHtml($html)->inlineCss()->render();
    }

    public function renderPdfFromHtml(string $html): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultPaperMargins', [-10, -10, -10, -10]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function contentTypeForExt(string $ext): string
    {
        if ($ext === 'pdf') {
            return 'application/pdf';
        }
        return 'application/vnd.ms-word';
    }

    /**
 * Handles logic for serving an existing uploaded file.
 */
 public function serveManualFile(Facture $facture, bool $returnBinary): Response|string|null
{
    $manualFile = $this->loadManualFile($facture);

    if ($manualFile === null) {
        return null;
    }

    if ($returnBinary) {
        return $manualFile['content'];
    }

    $contentType = $this->contentTypeForExt($manualFile['ext']);
    
    return response($manualFile['content'], 200)
        ->header('Content-Type', $contentType)
        ->header('Content-Disposition', 'attachment; filename="' . $manualFile['filename'] . '"');
}

/**
 * Handles logic for generating HTML and converting to PDF or DOC.
 */
public function generateAndServeFacture(array $montants, $facture, bool $returnBinary): Response|string
{
    // 1. Prepare Data
    $htmlInlined = $this->renderHtml([
        'facture'                    => $montants['facture'],
        'famille'                    => $montants['famille'],
        'enfants'                    => $montants['enfants'],
        'montantcotisation'          => $montants['montantcotisation'] ?? 0,
        'montantparticipation'       => $montants['montantparticipation'] ?? 0,
        'montantparticipationSeaska' => $montants['montantparticipationSeaska'] ?? 0,
        'montangarderie'             => $montants['montangarderie'] ?? 0,
        'montanttotal'               => $montants['montanttotal'] ?? 0,
        'totalPrevisionnel'          => $montants['totalPrevisionnel'] ?? 0,
    ]);

    // 2. Determine Format (Content & Meta)
    $isPdfState = $facture->getRawOriginal('etat') === 'verifier';
    
    if ($isPdfState) {
        $fileContent = $this->renderPdfFromHtml($htmlInlined);
        $contentType = 'application/pdf';
        $extension   = 'pdf';
    } else {
        $fileContent = $htmlInlined;
        $contentType = 'application/vnd.ms-word';
        $extension   = 'doc';
    }

    // 3. Early Exit: Binary
    if ($returnBinary) {
        return $fileContent;
    }

    // 4. Default Exit: HTTP Response
    $filename = sprintf('facture-%s.%s', $facture->idFacture ?? 'unknown', $extension);

    return response($fileContent, 200)
        ->header('Content-Type', $contentType)
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
}
}
