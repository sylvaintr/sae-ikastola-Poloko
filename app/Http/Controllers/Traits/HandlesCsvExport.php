<?php

namespace App\Http\Controllers\Traits;

use App\Models\Tache;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HandlesCsvExport
{
    private const DATE_FORMAT_CSV = 'd/m/Y';

    /**
     * Exporte une demande en CSV avec son historique.
     */
    public function exportCsv(Tache $demande): StreamedResponse
    {
        $demande->loadMissing(['historiques', 'realisateurs', 'documents']);

        $filename = $this->generateCsvFilename($demande->titre);
        $headers = $this->buildCsvHeaders($filename);

        $callback = function () use ($demande) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $this->writeDemandeSection($file, $demande);
            $this->writeHistoriqueSection($file, $demande->historiques);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Nettoie le titre pour le nom de fichier CSV.
     */
    private function generateCsvFilename(string $titre): string
    {
        $titreClean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $titre);
        $titreClean = preg_replace('/_+/', '_', $titreClean);
        $titreClean = trim($titreClean, '_');
        
        return $titreClean . '_demande_' . date('Y-m-d') . '.csv';
    }

    /**
     * Construit les en-têtes HTTP pour le CSV.
     */
    private function buildCsvHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
    }

    /**
     * Écrit la section demande dans le fichier CSV.
     */
    private function writeDemandeSection($file, Tache $demande): void
    {
        fputcsv($file, [__('demandes.export.demande_title')], ';');
        fputcsv($file, [], ';');
        fputcsv($file, [__('demandes.export.id'), $demande->idTache], ';');
        fputcsv($file, [__('demandes.export.titre'), $demande->titre], ';');
        fputcsv($file, [__('demandes.export.description'), $demande->description], ';');
        fputcsv($file, [__('demandes.export.type'), $demande->type ?? '—'], ';');
        fputcsv($file, [__('demandes.export.etat'), $demande->etat ?? '—'], ';');
        fputcsv($file, [__('demandes.export.urgence'), $demande->urgence ?? '—'], ';');
        fputcsv($file, [__('demandes.export.date_creation'), $this->formatDateForCsv($demande->dateD)], ';');
        fputcsv($file, [__('demandes.export.date_fin'), $this->formatDateForCsv($demande->dateF)], ';');
        fputcsv($file, [__('demandes.export.montant_previsionnel'), $this->formatMontantForCsv($demande->montantP)], ';');
        fputcsv($file, [__('demandes.export.montant_reel'), $this->formatMontantForCsv($demande->historiques->sum('depense'), true)], ';');
        
        $realisateurs = $demande->realisateurs->pluck('name')->join(', ');
        fputcsv($file, [__('demandes.export.realisateurs'), $realisateurs ?: '—'], ';');
        fputcsv($file, [], ';');
    }

    /**
     * Écrit la section historique dans le fichier CSV.
     */
    private function writeHistoriqueSection($file, Collection $historiques): void
    {
        fputcsv($file, [__('demandes.export.historique_title')], ';');
        fputcsv($file, [], ';');
        fputcsv($file, [
            __('demandes.history.columns.status.fr'),
            __('demandes.history.columns.date.fr'),
            __('demandes.history.columns.title.fr'),
            __('demandes.history.columns.assignment.fr'),
            __('demandes.history.columns.expense.fr'),
            __('demandes.modals.history_view.fields.description')
        ], ';');

        foreach ($historiques as $historique) {
            fputcsv($file, [
                $historique->statut,
                $this->formatDateForCsv($historique->date_evenement),
                $historique->titre,
                $historique->responsable ?? '—',
                $this->formatMontantForCsv($historique->depense, true),
                $historique->description ?? '—'
            ], ';');
        }
    }

    /**
     * Formate une date pour l'export CSV.
     */
    private function formatDateForCsv($date): string
    {
        return $date ? $date->format(self::DATE_FORMAT_CSV) : '—';
    }

    /**
     * Formate un montant pour l'export CSV.
     */
    private function formatMontantForCsv(?float $montant, bool $defaultToZero = false): string
    {
        if ($montant === null && !$defaultToZero) {
            return '—';
        }
        
        $value = $montant ?? 0;
        return number_format($value, 2, ',', ' ') . ' €';
    }
}

