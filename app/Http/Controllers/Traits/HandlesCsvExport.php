<?php
namespace App\Http\Controllers\Traits;

use App\Models\Tache;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HandlesCsvExport
{
    private const DATE_FORMAT_CSV = 'd/m/Y';

    /**
     * Méthode pour exporter une demande (Tache) au format CSV, en incluant les détails de la demande et son historique. Le CSV est généré à la volée et envoyé en réponse avec les en-têtes appropriés pour le téléchargement. Le nom du fichier CSV est formaté à partir du titre de la demande et de la date d'exportation.
     * @param Tache $demande La demande à exporter au format CSV
     * @return StreamedResponse Réponse de téléchargement du fichier CSV généré à la volée
     */
    public function exportCsv(Tache $demande): StreamedResponse
    {
        $demande->loadMissing(['historiques', 'realisateurs', 'documents']);

        $filename = $this->generateCsvFilename($demande->titre);
        $headers  = $this->buildCsvHeaders($filename);

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
     * Méthode pour générer un nom de fichier CSV à partir du titre de la demande et de la date d'exportation. Le titre est nettoyé pour remplacer les caractères spéciaux par des underscores, et le nom de fichier est formaté comme "Titre_demande_YYYY-MM-DD.csv".
     * @param string $titre Le titre de la demande à utiliser pour générer le nom du fichier CSV
     * @return string Nom de fichier CSV formaté à partir du titre et de la date d'exportation
     */
    private function generateCsvFilename(string $titre): string
    {
        $titreClean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $titre);
        $titreClean = preg_replace('/_+/', '_', $titreClean);
        $titreClean = trim($titreClean, '_');

        return $titreClean . '_demande_' . date('Y-m-d') . '.csv';
    }

    /**
     * Méthode pour construire les en-têtes HTTP pour le CSV.
     * @param string $filename Le nom du fichier CSV à utiliser dans les en-têtes
     * @return array Tableau des en-têtes HTTP pour la réponse CSV
     */
    protected function buildCsvHeaders(string $filename): array
    {
        return [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma'              => 'public',
            'Expires'             => '0',
        ];
    }

    /**
     * Méthode pour écrire la section demande dans le fichier CSV.
     * @param resource $file Le fichier CSV ouvert en écriture
     * @param Tache $demande La demande dont les données sont écrites dans le CSV
     * @return void
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
     * Méthode pour écrire la section historique dans le fichier CSV.
     * @param resource $file Le fichier CSV ouvert en écriture
     * @param Collection $historiques La collection des historiques à écrire dans le CSV
     * @return void
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
            __('demandes.modals.history_view.fields.description'),
        ], ';');

        foreach ($historiques as $historique) {
            fputcsv($file, [
                $historique->statut,
                $this->formatDateForCsv($historique->date_evenement),
                $historique->titre,
                $historique->responsable ?? '—',
                $this->formatMontantForCsv($historique->depense, true),
                $historique->description ?? '—',
            ], ';');
        }
    }

    /**
     * Formate une date pour l'export CSV. Si la date est nulle, retourne un tiret "—". Sinon, formate la date selon le format défini dans DATE_FORMAT_CSV (jour/mois/année).
     * @param \DateTimeInterface|null $date La date à formater
     * @return string La date formatée ou un tiret "—" si la date est nulle
     */
    protected function formatDateForCsv($date): string
    {
        return $date ? $date->format(self::DATE_FORMAT_CSV) : '—';
    }

    /**
     * Formate un montant pour l'export CSV. Si le montant est nul et que $defaultToZero est faux, retourne un tiret "—". Sinon, formate le montant avec deux décimales, une virgule comme séparateur décimal, un espace comme séparateur de milliers, et ajoute le symbole euro à la fin.
     * @param float|null $montant Le montant à formater
     * @param bool $defaultToZero Indique si un montant nul doit être traité comme zéro ou comme une valeur absente (affichée comme "—")
     * @return string Le montant formaté pour le CSV, ou un tiret "—" si le montant est nul et $defaultToZero est faux
     */
    protected function formatMontantForCsv(?float $montant, bool $defaultToZero = false): string
    {
        if ($montant === null && ! $defaultToZero) {
            return '—';
        }

        $value = $montant ?? 0;
        return number_format($value, 2, ',', ' ') . ' €';
    }
}
