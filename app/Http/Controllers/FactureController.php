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
use App\Models\Etre;
use App\Models\Utilisateur;
use Pelago\Emogrifier\CssInliner;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Services\FactureExporter;
use App\Services\FactureCalculator;


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
    private const PDF_APPLICATION = 'application/pdf';
    private const WORD_APPLICATION = 'application/vnd.ms-word';
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
        $montants = $this->factureCalculator->calculerMontantFacture($id);
        if ($montants instanceof RedirectResponse) {
            return $montants;
        }
        $facture = $montants['facture'];

        if ($facture->etat == "manuel") {
            // return le fichier de la facture
            $nomfichier = 'facture-' . $facture->idFacture;


            $chemin = self::DIR_FACTURES . $nomfichier . '.pdf';
            if (Storage::disk('public')->exists($chemin)) {


                $urlPublique = Storage::url(self::DIR_FACTURES . $nomfichier . '.pdf');
                return view('facture.show', [
                    'facture' => $facture,
                    'fichierpdf' => $urlPublique,
                ]);
            }

        }


        if (!$facture->previsionnel) {
            $montantRegulation = $this->calculerRegularisation($montants['famille']->idFamille);

        }

        return view('facture.show', [
            'facture' => $facture,
            'famille' => $montants['famille'],
            'enfants' => $montants['enfants'],
            'montangarderie' => $montants['montangarderie'] ?? 0,
            'montantcotisation' => $montants['montantcotisation'] ?? 0,
            'montantparticipation' => $montants['montantparticipation'] ?? 0,
            'montantparticipationSeaska' => $montants['montantparticipationSeaska'] ?? 0,
            'montanttotal' => $montants['montanttotal'] ?? 0,
            'totalPrevisionnel' => $montants['totalPrevisionnel'] ?? 0,
            'montantRegulation' => $montantRegulation ?? 0,
            'fichierpdf' => null,

        ]);
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

        // If the invoice has an uploaded/manual file, prefer serving that file
        if ($facture->etat === 'manuel' || $facture->etat === 'manuel verifier') {
            $manualFile = $this->factureExporter->loadManualFile($facture);
            if ($manualFile !== null) {

                $reponce = $returnBinary ? $manualFile['content'] : response($manualFile['content'], 200)
                    ->header('Content-Type', $this->factureExporter->contentTypeForExt($manualFile['ext']))
                    ->header('Content-Disposition', 'attachment; filename="' . $manualFile['filename'] . '"');

            }
        }

        $htmlInlined = $this->factureExporter->renderHtml([
            'facture' => $montants['facture'],
            'famille' => $montants['famille'],
            'enfants' => $montants['enfants'],
            'montantcotisation' => $montants['montantcotisation'] ?? 0,
            'montantparticipation' => $montants['montantparticipation'] ?? 0,
            'montantparticipationSeaska' => $montants['montantparticipationSeaska'] ?? 0,
            'montangarderie' => $montants['montangarderie'] ?? 0,
            'montanttotal' => $montants['montanttotal'] ?? 0,
            'totalPrevisionnel' => $montants['totalPrevisionnel'] ?? 0,
        ]);




        if ($facture->getRawOriginal('etat') === 'verifier') {

            $pdfexporter = $this->factureExporter->renderPdfFromHtml($htmlInlined);

            if ($returnBinary) {
                $reponce = $pdfexporter;
            } else {

                $reponce = response($pdfexporter, 200)
                    ->header('Content-Type', self::PDF_APPLICATION)
                    ->header('Content-Disposition', 'attachment; filename="facture-' . ($facture->idFacture ?? 'unknown') . '.pdf"');
            }
        } else {


            $reponce = response($htmlInlined, 200)
                ->header('Content-Type', self::WORD_APPLICATION)
                ->header('Content-Disposition', 'attachment; filename="facture-' . ($facture->idFacture ?? 'unknown') . '.doc"');
        }
        return $reponce;
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
        if ($facture->etat) {

            $famille = Famille::find($facture->idFamille);

            $mail = new FactureMail($facture, $famille);
            $piecejointe = $this->exportFacture($id, true);

            $mail->attachData($piecejointe, 'facture-' . $facture->idFacture . '.pdf', [
                'mime' => self::PDF_APPLICATION,
            ]);

            Mail::to($client->email)->send($mail);
            return redirect()->route('admin.facture.index')->with('success', 'facture.envoiersuccess');
        } else {
            return redirect()->route('admin.facture.index')->with('error', 'facture.envoiererror');
        }
    }

    public function validerFacture(string $id): RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        if ($facture->etat == 'brouillon') {

            $facture->etat = 'verifier';
        } elseif ($facture->etat == 'manuel') {
            $facture->etat = 'manuel verifier';
            // suprimer le document word ou odt
            $nomfichier = 'facture-' . $facture->idFacture;
            $extensionsPossibles = ['doc', 'docx', 'odt'];
            foreach ($extensionsPossibles as $ext) {
                $ancienChemin = self::DIR_FACTURES . $nomfichier . '.' . $ext;
                if (Storage::disk('public')->exists($ancienChemin)) {
                    Storage::disk('public')->delete($ancienChemin);
                }
            }
        } else {
            return redirect()->route('admin.facture.index', $facture->idFacture)->with('error', 'facture.dejasvalidee');
        }
        $facture->save();
        return redirect()->route('admin.facture.index', $facture->idFacture)->with('success', 'facture.validersuccess');
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



            $filename = 'facture-' . $facture->idFacture . '.' . $file->getClientOriginalExtension();
            $file->storeAs('factures', $filename, 'public');

            $chemin = 'factures/' . $filename;

            if (Storage::disk('public')->exists($chemin)) {

                $fichier = Storage::disk('public')->path($chemin);

                exec('libreoffice --headless --convert-to pdf ' . escapeshellarg($fichier) . ' --outdir ' . escapeshellarg(storage_path('app/public/factures/')));


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
        $familles = Famille::all();
        $mois = Carbon::now()->month;

        $previsionnel = !in_array($mois, [2, 8], true);
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
                }
            }
        }
    }




    /**
     *  calcule le montant de la regulation  pour une famille
     * @param mixed $idfamille identifiant de la famille
     * @return int monta de la regulation
     */
    public function calculerRegularisation(int $idfamille): int
    {
        $lastRegDate = Facture::where('idFamille', $idfamille)
            ->where('previsionnel', false)
            ->whereDate('dateC', '<>', Carbon::today())
            ->max('dateC');

        $startDate = $lastRegDate ? Carbon::parse($lastRegDate) : Carbon::create(2000, 1, 1);
        $facturesPrev = Facture::where('idFamille', $idfamille)
            ->where('previsionnel', true)
            ->where('dateC', '>=', $startDate)
            ->get();

        $totalPrev = 0;
        foreach ($facturesPrev as $facture) {
            $montantDetails = $this->factureCalculator->calculerMontantFacture($facture->idFacture);
            $totalPrev += $montantDetails['totalPrevisionnel']; // Null coalesce safety
        }

        $totalRegularisation = 0;


        $enfants = Famille::find($idfamille)->enfants;

        $cursorDate = $startDate->copy()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        while ($cursorDate->lte($endDate)) {


            $facture = Facture::where('idFamille', $idfamille)
                ->whereYear('dateC', $cursorDate->year)
                ->whereMonth('dateC', $cursorDate->month)
                ->first();

            if ($facture) {
                $montant = $this->factureCalculator->calculerMontantFacture($facture->idFacture);
                $totalRegularisation += ($montant['montantcotisation'] ?? 0)
                    + ($montant['montantparticipation'] ?? 0)
                    + ($montant['montantparticipationSeaska'] ?? 0);


                foreach ($enfants as $enfant) {

                    $monthStart = $cursorDate->copy()->startOfMonth();
                    $monthEnd = $cursorDate->copy()->endOfMonth();

                    $nbfoisgarderie = Etre::where('idEnfant', $enfant->idEnfant)
                        ->whereBetween('dateP', [$monthStart, $monthEnd])
                        ->whereHas('activite', function ($query) {
                            $query->where('activite', 'like', '%garderie%');
                        })
                        ->count();


                    if ($nbfoisgarderie > 8) {
                        $totalRegularisation += 20;
                    } elseif ($nbfoisgarderie > 0) {
                        $totalRegularisation += 10;
                    }
                }
            }

            $cursorDate->addMonth();
        }


        // si c'est positif la famille doit de l'argent
        // si c'est negatif l'ikastola doit de l'argent a la famille
        return $totalRegularisation - $totalPrev;
    }

}


