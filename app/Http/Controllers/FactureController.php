<?php
namespace App\Http\Controllers;

use App\Mail\Facture as FactureMail;
use App\Models\Facture;
use App\Models\Famille;
use App\Services\FactureCalculator;
use App\Services\FactureConversionService;
use App\Services\FactureExporter;
use App\Services\FactureFileService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

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

    private $factureCalculator;
    private $factureExporter;
    private $factureConversionService;
    private $factureFileService;
    public function __construct()
    {
        $this->factureCalculator        = app(FactureCalculator::class);
        $this->factureExporter          = app(FactureExporter::class);
        $this->factureConversionService = app(FactureConversionService::class);
        $this->factureFileService       = app(FactureFileService::class);
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
    public function show(string $id): View | RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $nomfichier = 'facture-' . $facture->idFacture;

        // return le fichier de la facture
        $chemin = self::DIR_FACTURES . $nomfichier . '.pdf';
        if (Storage::disk('public')->exists($chemin)) {
            $urlPublique = Storage::url($chemin);
            $return      = view('facture.show', [
                'fichierpdf' => $urlPublique,
            ]);
        } else {
            // File not found: redirect with error
            $return = redirect()->route('admin.facture.index')
                ->with('error', 'facture.fichierpdfintrouvable');
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
    public function exportFacture(string $id, bool $returnBinary = false): Response | RedirectResponse | string | null
    {
        $montants = app(FactureCalculator::class)->calculerMontantFacture($id);
        if ($montants instanceof RedirectResponse) {
            return $montants;
        }

        $facture = $montants['facture'] ?? Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $manualResponse = app(FactureExporter::class)->serveManualFile($facture, $returnBinary);
        if ($manualResponse !== null) {
            return $manualResponse;
        }

        return redirect()->route('admin.facture.index')->with('error', 'facture.fichierpdfintrouvable');

    }

    /**
     * Methode pour envoyer une facture par mail
     * @param string $id Identifiant de la facture à envoyer
     * @return RedirectResponse response de redirection vers la liste des factures
     */
    public function envoyerFacture(string $id): RedirectResponse | null
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        if ($facture->etat !== 'verifier') {
            return redirect()->route('admin.facture.index')->with('error', 'facture.envoiererror');
        }

        $client = $facture->utilisateur()->first();
        $mail = $this->prepareFactureMail($facture, $client);
        
        $piecejointe = $this->exportFacture($id, true);
        $mail->attachData($piecejointe, 'facture-' . $facture->idFacture . '.pdf', [
            'mime' => 'application/pdf',
        ]);

        Mail::to($client->email)->send($mail);
        return redirect()->route('admin.facture.index')->with('success', 'facture.envoiersuccess');
    }

    /**
     * Prepare the mail with the correct locale
     */
    private function prepareFactureMail($facture, $client)
    {
        $mail = new FactureMail($facture, $client);
        $langueDestinataire = $client->languePref ?? config('app.locale', 'fr');
        
        if (method_exists($mail, 'locale')) {
            $mail->locale($langueDestinataire);
        } else {
            app()->setLocale($langueDestinataire);
        }

        return $mail;
    }

    public function validerFacture(string $id): ?RedirectResponse
    {
        $response = null;

        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            $response = redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        } else {
            // On ne traite que si l'état n'est pas déjà validé
            if ($facture->etat != 'verifier') {
                // Use the conversion service synchronously (dependency-injected)
                $ok = app(FactureConversionService::class)->convertFactureToPdf($facture);

                if ($ok) {
                    // passer l'état à 'verifier'
                    $facture->etat = 'verifier';
                    $facture->save();
                    $response = redirect()->route('admin.facture.index')->with('success', 'facture.validersuccess');
                } else {
                    $response = redirect()->route('admin.facture.index')->with('error', 'facture.inexistantefile');
                }
            } else {
                $response = redirect()->route('admin.facture.index', $facture->idFacture)->with('error', 'facture.dejasvalidee');
            }
        }

        return $response;
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

        $response = app(FactureFileService::class)->processUploadedFile($request, $facture);

        if ($response === null) {
            $facture->etat = 'manuel';
            $facture->save();

            $response = redirect()->route('admin.facture.index')->with('success', 'facture.etatupdatesuccess');
        }

        return $response;
    }

    // file upload processing moved to FactureFileService

    // helpers moved to FactureFileService

    /**
     * Methode pour creer les factures mensuelles
     * si c'est le mois de fevrier ou aout les factures sont non previsionnelles
     * @return void
     */
    public function createFacture(): void
    {
        $previsionnel = $this->isPrevisionnel();

        Famille::chunk(100, function ($familles) use ($previsionnel) {
            foreach ($familles as $famille) {
                $this->createFacturesForFamille($famille, $previsionnel);
            }
        });
    }

    /**
     * Détermine si les factures du mois courant sont prévisionnelles
     */
    private function isPrevisionnel(): bool
    {
        $mois = Carbon::now()->month;
        return ! in_array($mois, [2, 8], true);
    }

    /**
     * Crée les factures pour une famille donnée
     */
    private function createFacturesForFamille(Famille $famille, bool $previsionnel): void
    {
        $parents = $famille->utilisateurs()->get();

        foreach ($parents as $parent) {
            if (($parent->pivot->parite ?? 0) > 0) {
                $this->createFactureForParent($famille, $parent, $previsionnel);
            }
        }
    }

    /**
     * Crée une facture pour un parent spécifique
     */
    private function createFactureForParent(Famille $famille, $parent, bool $previsionnel): void
    {
        $facture                = new Facture();
        $facture->idFamille     = $famille->idFamille;
        $facture->idUtilisateur = $parent->idUtilisateur;
        $facture->previsionnel  = $previsionnel;
        $facture->dateC         = now();
        $facture->etat          = 'brouillon';
        $facture->save();

        app(FactureExporter::class)->generateFactureToWord($facture);
        $this->createDummyDocxForTesting($facture);
    }

    /**
     * Crée un fichier DOCX factice en environnement de test
     */
    private function createDummyDocxForTesting(Facture $facture): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        $docxPath = storage_path('app/public/factures/facture-' . $facture->idFacture . '.docx');
        if (! file_exists($docxPath)) {
            @file_put_contents($docxPath, 'DUMMY_DOCX');
        }
    }

}
