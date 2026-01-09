<?php

namespace App\Services;

use App\Models\Facture;
use Pelago\Emogrifier\CssInliner;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

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
}
