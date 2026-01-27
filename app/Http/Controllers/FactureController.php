<?php

namespace App\Http\Controllers;

use App\Models\Enfant;
use App\Models\Facture;
use App\Models\Famille;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;
use App\Models\Pratiquer;
use App\Models\Utilisateur;
use Pelago\Emogrifier\CssInliner;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Services\FactureExporter;
use App\Services\FactureCalculator;
use PhpOffice\PhpWord\IOFactory;


/**
 * Class FactureController
 *
 * Contrôleur pour la gestion des factures.
 *
 * @package App\Http\Controllers
 */
class FactureController extends Controller
{
    private const DIR_FACTURES = 'factures/';
    private const ETAT_MANUEL_VERIFIER = 'manuel verifier';
    
    private $factureCalculator;
    private $factureExporter;

    public function __construct()
    {
        $this->factureCalculator = app(FactureCalculator::class);
        $this->factureExporter = app(FactureExporter::class);
    }

    /**
     * Methode pour afficher la liste des factures
     * @return View
     */
    public function index(): View
    {

        return view('facture.index');
    }


    /**
     * Methode pour afficher une facture specifique
     * @param string $id Identifiant de la facture à afficher
     * @return View|RedirectResponse
     */
    public function show(string $id): View|RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $nomfichier = 'facture-' . $facture->idFacture;

       

        if ($facture->etat =='verifier') {
            // return le fichier de la facture
            $chemin = self::DIR_FACTURES . $nomfichier . '.pdf';
            if (Storage::disk('public')->exists($chemin)) {
                $urlPublique = Storage::url($chemin);
                $return = view('facture.show', [
                    'fichierpdf' => $urlPublique,
                ]);
            } else {
                // File not found: redirect with error
                $return = redirect()->route('admin.facture.index')
                    ->with('error', 'facture.fichierpdfintrouvable');
            }

            }else {
                $extensionsPossibles = ['doc', 'docx', 'odt'];
                $extensionTrouvee = null;
                $fichierWordPath = null;
                foreach ($extensionsPossibles as $ext) {
                    $chemin = self::DIR_FACTURES . $nomfichier . '.' . $ext;
                    if (Storage::disk('public')->exists($chemin)) {
                        $fichierWordPath = Storage::disk('public')->path($chemin);
                        $extensionTrouvee = $ext;
                        break;
                    }
                }

                if ($fichierWordPath === null) {
                    return redirect()->route('admin.facture.index')->with('error', 'facture.fichierintrouvable');
                }

                // Si c'est déjà un DOCX on l'utilise directement, sinon on essaye de convertir (odt/doc -> docx)
                if ($extensionTrouvee === 'docx') {
                    $originalDocxPath = $fichierWordPath;
                } else {
                    $tmpDir = sys_get_temp_dir();
                    $basename = pathinfo($fichierWordPath, PATHINFO_FILENAME);
                    $convertedPath = $tmpDir . DIRECTORY_SEPARATOR . $basename . '.docx';

                    // Nettoyage préventif
                    if (file_exists($convertedPath)) {
                        @unlink($convertedPath);
                    }

                    $cmd = 'export HOME=/tmp && libreoffice --headless --convert-to docx ' . escapeshellarg($fichierWordPath) . ' --outdir ' . escapeshellarg($tmpDir) . ' 2>&1';
                    $output = [];
                    $returnVar = 0;
                    exec($cmd, $output, $returnVar);

                    if (!file_exists($convertedPath)) {
                        // Conversion échouée -> erreur visible à l'utilisateur
                        \Illuminate\Support\Facades\Log::error("Échec conversion LibreOffice pour facture $id", ['cmd_output' => $output]);
                        return redirect()->route('admin.facture.index')->with('error', 'facture.conversionerreur');
                    }

                    $originalDocxPath = $convertedPath;
                }
                $originalDocxPath = storage_path('app/public/factures/facture-' . $id . '.docx');
                $docxPath = $this->sanitizeDocxLanguageCodes($originalDocxPath);
                $phpWord = IOFactory::load($docxPath);

                // Conversion en HTML
                $xmlWriter = IOFactory::createWriter($phpWord, 'HTML');

                // Capture du HTML
                ob_start();
                $xmlWriter->save("php://output");
                $htmlContent = ob_get_clean();
                $inlinedHtml = CssInliner::fromHtml($htmlContent)->inlineCss()->render();
                $return = view('facture.show', [
                    
                    'inlinedHtml' => $inlinedHtml,
                ]);

            }
            
            return $return;

        
    }

    /**
     * Permet de gérer le corps du tableau de factures en AJAX pour DataTables
     * @return JsonResponse
     */
    public function facturesData(): JsonResponse
    {
        $query = Facture::query();

        return DataTables::of($query)
            ->addColumn('titre', function ($facture) {
                return "Facture {$facture->idFacture}";
            })
            ->addColumn('actions', function ($facture) {
                return view('facture.template.colonne-action', compact('facture'));
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Methode pour exporter une facture en PDF ou Word
     * @param string $id Identifiant de la facture à exporter
     * @param bool $returnBinary Indique si la méthode doit retourner le binaire du fichier (true) ou une réponse HTTP (false)
     * @return Response|RedirectResponse response contenant le fichier exporté
     */
    public function exportFacture(string $id, bool $returnBinary = false): Response|RedirectResponse|string
    {

        $montants = $this->factureCalculator->calculerMontantFacture($id);

        if ($montants instanceof RedirectResponse) {
            return $montants;
        }

        $facture = $montants['facture'];

    
        if (in_array($facture->etat, ['manuel', self::ETAT_MANUEL_VERIFIER], true)) {
        
            $manualResponse = $this->factureExporter->serveManualFile($facture, $returnBinary);
            if ($manualResponse) {
                return $manualResponse;
            }
        } else {

            return $this->factureExporter->generateAndServeFacture($montants, $facture, $returnBinary);
        }

    }


    /**
     * Methode pour envoyer une facture par mail
     * @param string $id Identifiant de la facture à envoyer
     * @return RedirectResponse response de redirection vers la liste des factures
     */
    public function envoyerFacture(string $id): RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $client = $facture->famille()->first()->utilisateurs()->first();
        if (in_array($facture->etat, ['verifier', self::ETAT_MANUEL_VERIFIER], true)) {

            $famille = Famille::find($facture->idFamille);

            $mail = new FactureMail($facture, $famille);
            $piecejointe = $this->exportFacture($id, true);

            $mail->attachData($piecejointe, 'facture-' . $facture->idFacture . '.pdf', [
                'mime' => 'application/pdf',
            ]);

            Mail::to($client->email)->send($mail);
            return redirect()->route('admin.facture.index')->with('success', 'facture.envoiersuccess');
        } else {
            return redirect()->route('admin.facture.index')->with('error', 'facture.envoiererror');
        }
    }

    public function validerFacture(string $id): ?RedirectResponse
{
    $facture = Facture::find($id ?? null);
    if ($facture === null) {
        return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
    }

    // On ne traite que si l'état n'est pas déjà validé
    if ($facture->etat != 'verifier') {

        $nomfichier = 'facture-' . $facture->idFacture;
        $extensionsPossibles = ['doc', 'docx', 'odt'];
        $outputDir = storage_path('app/public/factures/');

        // Variable pour savoir si on a réussi la conversion
        $conversionReussie = false;

        foreach ($extensionsPossibles as $ext) {
            $ancienCheminRelatif = self::DIR_FACTURES . $nomfichier . '.' . $ext;

            // Si le fichier Word existe
            if (Storage::disk('public')->exists($ancienCheminRelatif)) {
                
                $inputPath = Storage::disk('public')->path($ancienCheminRelatif);
                $pdfCible = $outputDir . $nomfichier . '.pdf';

                // 1. NETTOYAGE PRÉVENTIF : Supprimer l'ancien PDF s'il existe déjà
                // (Cela évite les erreurs de permission si root a créé le fichier précédent)
                if (file_exists($pdfCible)) {
                    unlink($pdfCible);
                }

                // 2. COMMANDE DE CONVERSION
                // "export HOME=/tmp" est OBLIGATOIRE pour que www-data puisse lancer LibreOffice
                $command = 'export HOME=/tmp && libreoffice --headless --convert-to pdf ' . escapeshellarg($inputPath) . ' --outdir ' . escapeshellarg($outputDir) . ' 2>&1';

                $output = [];
                $returnVar = 0;
                exec($command, $output, $returnVar);

                // 3. VÉRIFICATION DU RÉSULTAT
                if (file_exists($pdfCible)) {
                    // Succès : Le PDF est là, on peut supprimer le Word
                    Storage::disk('public')->delete($ancienCheminRelatif);
                    $conversionReussie = true;
                    
                    // On arrête la boucle, on a trouvé et converti le fichier
                    break;
                } else {
                    // Échec : On loggue l'erreur pour le développeur
                    \Illuminate\Support\Facades\Log::error("Échec conversion LibreOffice Facture $id", ['cmd_output' => $output]);
                }
            }
        }

        if ($conversionReussie) {
            // Mise à jour de l'état seulement si le PDF a été créé
            $facture->etat = 'verifier';
            $facture->save();
            return redirect()->route('admin.facture.index')->with('success', 'Facture validée et convertie en PDF avec succès.');
        } else {
            // Si on sort de la boucle sans avoir réussi
            return redirect()->route('admin.facture.index')->with('error', 'Impossible de convertir le fichier Word. Vérifiez qu\'il existe ou consultez les logs.');
        }

    } else {
        return redirect()->route('admin.facture.index', $facture->idFacture)->with('error', 'facture.dejasvalidee');
    }
}

   

    public function update(Request $request, string $id): RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $request->validate([
            'facture' => 'nullable|file|mimes:doc,docx,odt|max:2048',
        ]);

        if ($request->hasFile('facture')) {
            $file = $request->file('facture');

            // Vérification des premiers octets (magic bytes)
            $fh = fopen($file->getRealPath(), 'rb');
            $bytes = fread($fh, 8);
            fclose($fh);

            $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"; // .doc (OLE)
            $zipHeader = "\x50\x4B\x03\x04"; // .docx / .odt (ZIP)

            if (!(strpos($bytes, $oleHeader) === 0 || strpos($bytes, $zipHeader) === 0)) {

                return redirect()->route('admin.facture.index')->with('error', 'facture.invalidfile');
            }

            $extensionsPossibles = ['doc', 'docx', 'odt'];

            foreach ($extensionsPossibles as $ext) {
                $ancienChemin = 'factures/facture-' . $facture->idFacture . '.' . $ext;

                // On vérifie sur le disque 'public' car vous utilisez storeAs(..., ..., 'public') plus bas
                if (Storage::disk('public')->exists($ancienChemin)) {
                    Storage::disk('public')->delete($ancienChemin);
                }
            }

            // Enregistrer le fichier uploadé dans storage/app/public/factures
            try {
                $extension = strtolower($file->getClientOriginalExtension() ?? $file->extension());
                if (!in_array($extension, $extensionsPossibles, true)) {
                    $extension = 'docx';
                }
                $filename = 'facture-' . $facture->idFacture . '.' . $extension;
                $stored = $file->storeAs('public/factures', $filename);
                if ($stored === false) {
                    return redirect()->route('admin.facture.index')->with('error', 'facture.uploadfail');
                }
                // assurer visibilité publique
                Storage::disk('public')->setVisibility('factures/' . $filename, 'public');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Erreur lors de l\'upload facture', ['err' => $e->getMessage()]);
                return redirect()->route('admin.facture.index')->with('error', 'facture.uploadfail');
            }



        }
        $facture->etat = 'manuel';
        $facture->save();

        return redirect()->route('admin.facture.index')->with('success', 'facture.etatupdatesuccess');
    }


    /**
     * Methode pour creer les factures mensuelles
     * si c'est le mois de fevrier ou aout les factures sont non previsionnelles
     * @return void
     */
    public function createFacture(): void
    {
        $mois = Carbon::now()->month;

        $previsionnel = !in_array($mois, [2, 8], true);
        $familles = Famille::get();
        foreach ($familles as $famille) {
            $parents = $famille->utilisateurs()->get();
            foreach ($parents as $parent) {
                if (($parent->pivot->parite ?? 0) > 0) {
                    $facture = new Facture();
                    $facture->idFamille = $famille->idFamille;
                    $facture->idUtilisateur = $parent->idUtilisateur;
                    $facture->previsionnel = $previsionnel;
                    $facture->dateC = now();
                    $facture->etat = 'brouillon';
                    $facture->save();
                    $this->factureExporter->generateFactureToWord($facture);
                }
            }
        }
    }






}
