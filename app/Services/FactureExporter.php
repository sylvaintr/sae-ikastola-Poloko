<?php
namespace App\Services;

use App\Models\Facture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class FactureExporter
{

    /**
     * Méthode pour obtenir le contenu binaire d'un fichier de facture existant, ainsi que son extension et un nom de fichier suggéré. Cette méthode recherche d'abord le fichier de la facture dans les formats PDF (si la facture est vérifiée) ou Word/ODT (si la facture n'est pas encore vérifiée), en utilisant l'identifiant de la facture pour construire le nom du fichier. Si un fichier correspondant est trouvé, elle retourne un tableau contenant le contenu binaire du fichier, son extension et un nom de fichier suggéré pour le téléchargement. Si aucun fichier n'est trouvé, elle retourne null.
     * @param Facture $facture La facture pour laquelle obtenir le fichier
     * @return array{content: string, ext: string, filename: string}|null Un tableau contenant le contenu binaire du fichier, son extension et un nom de fichier suggéré, ou null si aucun fichier n'est trouvé
     */
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
     * Méthode pour servir un fichier de facture existant en réponse à une requête HTTP. Cette méthode utilise la méthode `getLinkFarctureFile` pour obtenir le contenu binaire du fichier de la facture, son extension et un nom de fichier suggéré. Si aucun fichier n'est trouvé, elle retourne null. Si un fichier est trouvé et que le paramètre `$returnBinary` est vrai, elle retourne directement le contenu binaire du fichier. Sinon, elle retourne une réponse HTTP avec le contenu du fichier et les en-têtes appropriés pour forcer le téléchargement du fichier par le navigateur, en utilisant le nom de fichier suggéré.
     * @param Facture $facture La facture pour laquelle servir le fichier
     * @param bool $returnBinary Si true, retourne directement le contenu binaire du fichier, sinon retourne une réponse HTTP pour le téléchargement du fichier
     * @return Response|string|null Une réponse HTTP avec le contenu du fichier et les en-têtes de téléchargement, le contenu binaire du fichier, ou null si aucun fichier n'est trouvé
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

    /**
     * Méthode pour générer un fichier de facture au format Word en utilisant un modèle et les données de la facture. Cette méthode utilise la bibliothèque PhpWord pour remplir un modèle Word avec les informations de la facture, telles que l'identifiant, la date, le nom du parent, le nombre d'enfants, les montants calculés, etc. Le fichier généré est ensuite enregistré dans le stockage public de l'application. Après la génération du fichier Word, la méthode appelle le service de conversion pour convertir le fichier en PDF. Si une erreur survient lors de la génération du fichier Word, elle est enregistrée dans les logs et la méthode retourne null.
     * @param Facture $facture La facture pour laquelle générer le fichier Word
     * @return void|null Retourne null si une erreur survient lors de la génération du fichier Word, sinon ne retourne rien
     * @throws \PhpOffice\PhpWord\Exception\ExceptionInterface Si une erreur survient lors de l'utilisation de la bibliothèque PhpWord pour générer le fichier Word
     */
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
            $totalTtcNumeric = $valeurPrevisionnelleNumeric * ($pariteNumeric / 100);

            $templateProcessor->setValue('pariter', $pariteNumeric);
            $templateProcessor->setValue('totalPrevisionnel', $valeurPrevisionnelle);
            $templateProcessor->setValue('total', number_format($totalTtcNumeric, 2, ',', ' '));

            $templateProcessor->saveAs($docxPath);

            // convert to PDF
            $factureConversionService = app()->make('App\Services\FactureConversionService');
            $factureConversionService->convertFactureToPdf($facture);

        } catch (\Throwable $e) {
            // If TemplateProcessor fails for any reason, log and return null
            \Illuminate\Support\Facades\Log::error('FactureExporter: template error', ['err' => $e->getMessage()]);
            return null;
        }

    }

}
