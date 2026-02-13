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
    /**
     * Constructeur du FactureController, qui initialise les services nécessaires à la gestion des factures
     * en utilisant l'injection de dépendances pour obtenir les instances des services FactureCalculator, FactureExporter, FactureConversionService et FactureFileService
     */
    public function __construct()
    {
        $this->factureCalculator        = app(FactureCalculator::class);
        $this->factureExporter          = app(FactureExporter::class);
        $this->factureConversionService = app(FactureConversionService::class);
        $this->factureFileService       = app(FactureFileService::class);
    }

    /**
     * Methode pour afficher la liste des factures
     * @return View la vue affichant la liste des factures
     */
    public function index(): View
    {

        return view('facture.index');
    }

    /**
     * Methode pour afficher une facture specifique
     * @param string $id Identifiant de la facture à afficher
     * @return View|RedirectResponse la vue affichant la facture spécifiée ou une réponse de redirection vers la liste des factures avec un message d'erreur si la facture n'existe pas ou si le fichier PDF de la facture est introuvable
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
     * @return JsonResponse réponse JSON contenant les données des factures formatées pour DataTables, avec des colonnes supplémentaires pour le titre de la facture et les actions disponibles (affichage, validation, envoi par mail)
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
     * @return Response|RedirectResponse response contenant le fichier exporté ou le binaire du fichier si $returnBinary est true, ou une réponse de redirection vers la liste des factures avec un message d'erreur si la facture n'existe pas ou si le fichier de la facture est introuvable
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
     * @return RedirectResponse response de redirection vers la liste des factures avec un message de succès ou d'erreur selon le résultat de l'envoi de la facture par mail
     */
    public function envoyerFacture(string $id): RedirectResponse | null
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $client = $facture->utilisateur()->first();
        if ($facture->etat === 'verifier') {

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

    /**
     * Methode pour valider une facture en traitant le fichier de la facture et en mettant à jour son état
     * @param string $id Identifiant de la facture à valider
     * @return RedirectResponse la réponse de redirection vers la liste des factures avec un message de succès ou d'erreur selon le résultat de la validation de la facture
     */
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
                $ok = $this->factureConversionService->convertFactureToPdf($facture);

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

    /**
     * Methode pour valider une facture en traitant le fichier de la facture et en mettant à jour son état
     * @param Request $request la requête HTTP contenant les données de validation de la facture
     * @param string $id Identifiant de la facture à valider
     * @return RedirectResponse la réponse de redirection vers la liste des factures avec un message de succès ou d'erreur selon le résultat de la validation de la facture
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $request->validate([
            'facture' => 'nullable|file|mimes:doc,docx,odt|max:2048',
        ]);

        $response = $this->factureFileService->processUploadedFile($request, $facture);

        if ($response === null) {
            $facture->etat = 'manuel';
            $facture->save();

            $response = redirect()->route('admin.facture.index')->with('success', 'facture.etatupdatesuccess');
        }

        return $response;
    }

    /**
     * Methode pour creer les factures mensuelles
     * si le mois en cours est un mois de régularisation, les factures créées seront des régularisations (previsionnel = false), sinon ce seront des factures prévisionnelles (previsionnel = true)
     * genère une facture pour chaque parent de chaque famille, en utilisant le service FactureExporter pour créer le fichier de la facture et en enregistrant les informations de la facture dans la base de données
     * les mois de régularisation sont définis dans le .env ou dans le config/facture.php via la variable MONTHS_REGULATING, qui doit être un tableau d'entiers représentant les mois de l'année (ex: [2,8] pour février et août)
     * @return void
     */
    public function createFacture(): void
    {
        $mois = Carbon::now()->month;

        $previsionnel = ! in_array($mois, config('facture.MONTHS_REGULATING', [2, 8]), true);
        Famille::chunk(100, function ($familles) use ($previsionnel) {
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
        });
    }

}
