<?php
namespace App\Http\Controllers;

use App\Mail\Facture as FactureMail;
use App\Models\Facture;
use App\Models\Famille;
use App\Services\FactureCalculator;
use App\Services\FactureConversionService;
use App\Services\FactureExporter;
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
    private const DIR_FACTURES         = 'factures/';
    private const ETAT_MANUEL_VERIFIER = 'manuel verifier';

    private $factureCalculator;
    private $factureExporter;
    private $factureConversionService;

    public function __construct()
    {
        $this->factureCalculator        = app(FactureCalculator::class);
        $this->factureExporter          = app(FactureExporter::class);
        $this->factureConversionService = app(FactureConversionService::class);
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

        $montants = $this->factureCalculator->calculerMontantFacture($id);

        if ($montants instanceof RedirectResponse) {
            return $montants;
        }

        $facture = $montants['facture'];

        $manualResponse = $this->factureExporter->serveManualFile($facture, $returnBinary);
        if ($manualResponse) {
            return $manualResponse;
        }

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
        $client = $facture->utilisateur()->first();
        if (in_array($facture->etat, ['verifier', self::ETAT_MANUEL_VERIFIER], true)) {

            $mail = new FactureMail($facture, $client);

            // Déterminer la langue préférée du destinataire et l'appliquer au Mailable
            $langueDestinataire = $client->languePref ?? config('app.locale', 'fr');
            if (method_exists($mail, 'locale')) {
                $mail->locale($langueDestinataire);
            } else {
                app()->setLocale($langueDestinataire);
            }

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
            // Use the conversion service synchronously (dependency-injected)
            $ok = $this->factureConversionService->convertFactureToPdf($facture);

            if ($ok) {
                // passer l'état à 'verifier'
                $facture->etat = 'verifier';
                $facture->save();
                return redirect()->route('admin.facture.index')->with('success', 'Facture validée et convertie en PDF avec succès.');
            }

            return redirect()->route('admin.facture.index')->with('error', 'Impossible de convertir le fichier Word. Vérifiez qu\'il existe ou consultez les logs.');
        }

        return redirect()->route('admin.facture.index', $facture->idFacture)->with('error', 'facture.dejasvalidee');
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
            $fh    = fopen($file->getRealPath(), 'rb');
            $bytes = fread($fh, 8);
            fclose($fh);

            $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"; // .doc (OLE)
            $zipHeader = "\x50\x4B\x03\x04";                 // .docx / .odt (ZIP)

            if (! (strpos($bytes, $oleHeader) === 0 || strpos($bytes, $zipHeader) === 0)) {

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
                if (! in_array($extension, $extensionsPossibles, true)) {
                    $extension = 'docx';
                }
                $filename = 'facture-' . $facture->idFacture . '.' . $extension;
                $stored   = $file->storeAs('public/factures', $filename);
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

        $previsionnel = ! in_array($mois, [2, 8], true);
        $familles     = Famille::get();
        foreach ($familles as $famille) {
            $parents = $famille->utilisateurs()->get();
            foreach ($parents as $parent) {
                if (($parent->pivot->parite ?? 0) > 0) {
                    $facture                = new Facture();
                    $facture->idFamille     = $famille->idFamille;
                    $facture->idUtilisateur = $parent->idUtilisateur;
                    $facture->previsionnel  = $previsionnel;
                    $facture->dateC         = now();
                    $facture->etat          = 'brouillon';
                    $facture->save();
                    $this->factureExporter->generateFactureToWord($facture);
                }
            }
        }
    }

}
