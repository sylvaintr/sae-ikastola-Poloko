<?php

namespace App\Http\Controllers;

use App\Models\Enfant;
use App\Models\Facture;
use App\Models\Famille;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\Facture as FactureMail;
use App\Models\Etre;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Collection;
use Pelago\Emogrifier\CssInliner;
use Dompdf\Dompdf;
use Dompdf\Options;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {

        return view('facture.index');
    }


    /**
     * Display the specified resource.
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
        ]);
    }

    /**
     * permet de gérer le corps du tableau de factures en AJAX pour DataTables
     */
    public function  facturesData(): JsonResponse
    {
        $query = Facture::query();

        return DataTables::of($query)
            ->addColumn('titre', function ($facture) {
                return "Facture {$facture->idFacture}";
            })
            ->addColumn('etat', function ($facture) {
                return $facture->etat ? 'Vérifiée' : 'Brouillon';
            })
            ->addColumn('actions', function ($facture) {
                return view('facture.template.colonne-action', compact('facture'));
            })
            ->rawColumns(['actions'])
            ->make(true);
    }


    public function exportFacture(string $id): Response|RedirectResponse
    {

        $montants = $this->calculerMontantFacture($id);
        if ($montants instanceof RedirectResponse) {
            return $montants;
        }

        $facture = $montants['facture'];



        $content = view('facture.template.facture-html', [
            'facture' => $montants['facture'],
            'famille' => $montants['famille'],
            'enfants' => $montants['enfants'],
            'montantcotisation' => $montants['montantcotisation'] ?? 0,
            'montantparticipation' => $montants['montantparticipation'] ?? 0,
            'montantparticipationSeaska' => $montants['montantparticipationSeaska'] ?? 0,
            'montangarderie' => $montants['montangarderie'] ?? 0,
            'montanttotal' => $montants['montanttotal'] ?? 0
        ])->render();
        $htmlInlined = CssInliner::fromHtml($content)->inlineCss()->render();
        if (is_object($facture) && $facture->etat) {

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultPaperMargins', [10, 10, 10, 10]);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlInlined);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();


            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="facture-' . ($facture->idFacture ?? 'unknown') . '.pdf"');
        } else {

            return response($htmlInlined, 200)
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="facture-' . ($facture->idFacture ?? 'unknown') . '.doc"');
        }
    }



    public function envoyerFacture(string $id): RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $client = Utilisateur::find($facture->idUtilisateur);
        if ($facture->etat) {

            Mail::to($client->email)->send(new FactureMail($facture));
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
     * @param Facture $facture
     * @param Famille $famille
     * @param \Illuminate\Database\Eloquent\Collection<int, Enfant> $enfants
     * @return array<int, float> associative array with keys:
     *      montantcotisation, montantparticipation, montantparticipationSeaska, montangarderie, montanttotal
     */
    private function calculerMontantFacture(string $id): array|RedirectResponse
    {

        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();

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
        /** @var Enfant $enfant */
        foreach ($enfants as $enfant) {
            if ($facture->previsionnel) {
                $nbfoisgarderie = $enfant->nbFoisGarderie;
            } else {
                $debutMois = $facture->dateC->copy()->startOfMonth();
                $finMois = $facture->dateC->copy()->endOfMonth();



                $nbfoisgarderie =  Etre::where('idEnfant', $enfant->idEnfant)
                    ->whereBetween('dateP', [$debutMois, $finMois])
                    ->whereHas('activite', function ($query) {
                        $query->where('activite', 'like', '%garderie%');
                    })
                    ->count();
            }

            if ($nbfoisgarderie > 0 && $nbfoisgarderie <= 8) {
                $montangarderie += 10;
            } elseif ($nbfoisgarderie > 8) {
                $montangarderie += 20;
            } else {
                $montangarderie += 0;
            }
        }

        $montanttotal = $montangarderie + $montantcotisation + $montantparticipation + $montantparticipationSeaska;

        return [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => $enfants,
            'montantcotisation' => $montantcotisation,
            'montantparticipation' => $montantparticipation,
            'montantparticipationSeaska' => $montantparticipationSeaska,
            'montangarderie' => $montangarderie,
            'montanttotal' => $montanttotal,
        ];
    }
}
