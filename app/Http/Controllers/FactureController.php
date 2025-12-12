<?php

namespace App\Http\Controllers;

use App\Models\Enfant;
use App\Models\Facture;
use App\Models\Famille;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;
use App\Models\Etre;
use App\Models\Utilisateur;
use Pelago\Emogrifier\CssInliner;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Class FactureController
 *
 * Contrôleur pour la gestion des factures.
 *
 * @package App\Http\Controllers
 */
class FactureController extends Controller
{
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
        $montants = $this->calculerMontantFacture($id);
        if ($montants instanceof RedirectResponse) {
            return $montants;
        }

        return view('facture.show', [
            'facture' => $montants['facture'],
            'famille' => $montants['famille'],
            'enfants' => $montants['enfants'],
            'montangarderie' => $montants['montangarderie'] ?? 0,
            'montantcotisation' => $montants['montantcotisation'] ?? 0,
            'montantparticipation' => $montants['montantparticipation'] ?? 0,
            'montantparticipationSeaska' => $montants['montantparticipationSeaska'] ?? 0,
            'montanttotal' => $montants['montanttotal'] ?? 0,
            'totalPrevisionnel' => $montants['totalPrevisionnel'] ?? 0,
            
        ]);
    }

    /**
     * Permet de gérer le corps du tableau de factures en AJAX pour DataTables
     * @return JsonResponse
     */
    public function  facturesData(): JsonResponse
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
      


        $montants = $this->calculerMontantFacture($id);
        if ($montants instanceof RedirectResponse) {
            return $montants;
        }

        $facture = $montants['facture'];

        if($facture->etat == 'manuel'){

        }

        $content = view('facture.template.facture-html', [
            'facture' => $montants['facture'],
            'famille' => $montants['famille'],
            'enfants' => $montants['enfants'],
            'montantcotisation' => $montants['montantcotisation'] ?? 0,
            'montantparticipation' => $montants['montantparticipation'] ?? 0,
            'montantparticipationSeaska' => $montants['montantparticipationSeaska'] ?? 0,
            'montangarderie' => $montants['montangarderie'] ?? 0,
            'montanttotal' => $montants['montanttotal'] ?? 0,
            'totalPrevisionnel' => $montants['totalPrevisionnel'] ?? 0,
        ])->render();
        $htmlInlined = CssInliner::fromHtml($content)->inlineCss()->render();
        if (is_object($facture) && $facture->etat === 'verifier') {

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $options->set('defaultPaperMargins', [-10, -10, -10, -10]);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlInlined);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            if ($returnBinary) {
                return $dompdf->output();
            }



            $reponce = response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="facture-' . ($facture->idFacture ?? 'unknown') . '.pdf"');
        } else {
            
            
            $reponce = response($htmlInlined, 200)
                ->header('Content-Type', 'application/vnd.ms-word')
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
                'mime' => 'application/pdf',
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
        $facture->etat = true;
        $facture->save();
        return redirect()->route('admin.facture.index', $facture->idFacture)->with('success', 'facture.validersuccess');
    }



    /**
     * Calculate invoice amounts.
     * @param string $id Identifiant de la facture à calculer
     * @return array<int, float> tableau associatif contenant les montants calculés :
     *      montantcotisation, montantparticipation, montantparticipationSeaska, montangarderie, montanttotal,facture, famille, enfants
     */
    private function calculerMontantFacture(string $id): array|RedirectResponse
    {

        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $famille = $facture->famille()->first();
        $enfants = $famille->enfants()->get();

        $montantcotisation = 0;
        switch ($enfants->count()) {
            case 0:
                $montantcotisation = 0;
                break;
            case 1:
                $montantcotisation = 45;
                break;
            case 2:
                $montantcotisation = 65;
                break;
            default:
                $montantcotisation = 75;
                break;
        }

        // calcul du montant de la participation
        $montantparticipation = $enfants->count() * 9.65;


        // calcul du montant de la partisapation seaska
        if ($famille->aineDansAutreSeaska) {
            $montantparticipationSeaska = 7.70;
        } else {
            $montantparticipationSeaska = 0;
        }




        // calcul du montant de la garderie
        $montangarderie = 0;
        $nbfoisgarderie = 0;
        $montangarderieprev = 0;
        /** @var Enfant $enfant */
        foreach ($enfants as $enfant) {
            
                $montangarderieprev = $enfant->nbFoisGarderie;
           
                $debutMois = $facture->dateC->copy()->startOfMonth();
                $finMois = $facture->dateC->copy()->endOfMonth();



                $nbfoisgarderie =  Etre::where('idEnfant', $enfant->idEnfant)
                    ->whereBetween('dateP', [$debutMois, $finMois])
                    ->whereHas('activite', function ($query) {
                        $query->where('activite', 'like', '%garderie%');
                    })
                    ->count();
            

            if ($nbfoisgarderie > 0 && $nbfoisgarderie <= 8) {
                $montangarderieprev += 10;
                $montangarderie += 10;
            } elseif ($nbfoisgarderie > 8) {
                $montangarderieprev += 20;
                $montangarderie += 20;
            } else {
                $montangarderieprev += 0;
                $montangarderie += 0;
            }
        }

        $montanttotal =   ( $facture->etat !=='verifier' ? $montangarderie : $montangarderieprev )+ $montantcotisation + $montantparticipation + $montantparticipationSeaska;
        $montanttotalprev =   $montangarderieprev + $montantcotisation + $montantparticipation + $montantparticipationSeaska;
        return [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => $enfants,
            'montantcotisation' => $montantcotisation,
            'montantparticipation' => $montantparticipation,
            'montantparticipationSeaska' => $montantparticipationSeaska,
            'montangarderie' => $montangarderie,
            'montanttotal' => $montanttotal,
            'totalPrevisionnel' => $montanttotalprev,
        ];
    }


    public function updateEtat(Request $request, string $id): RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }

        $request->validate([
            'facture' => 'required|file|mimes:doc,docx,odt'
        ]);

        $facture->etat = $request->input('etat');
        $facture->save();

        return redirect()->route('admin.facture.index')->with('success', 'facture.etatupdatesuccess');
    }
}
