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

        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();


        $montants = $this->calculerMontantFacture($facture, $famille, $enfants);
        $montantcotisation = $montants['montantcotisation'];
        $montantparticipation = $montants['montantparticipation'];
        $montantparticipationSeaska = $montants['montantparticipationSeaska'];
        $montangarderie = $montants['montangarderie'];
        $montanttotal = $montants['montanttotal'];

        return view('facture.show', [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => $enfants,
            'montangarderie' => $montangarderie ?? 0,
            'montantcotisation' => $montantcotisation ?? 0,
            'montantparticipation' => $montantparticipation ?? 0,
            'montantparticipationSeaska' => $montantparticipationSeaska ?? 0,
            'montanttotal' => $montanttotal ?? 0,

        ]);
    }

    /**
     * permet de gérer le corps du tableau de factures en AJAX pour DataTables
     */
    public function  facturesData(): JsonResponse
    {
        $query = Facture::query();

        return DataTables::of($query)
            ->addColumn('titre', fn($facture) => "Facture {$facture->idFacture}")
            ->addColumn('etat', fn($facture) => $facture->etat ? 'Vérifiée' : 'Brouillon')
            ->addColumn('actions', function ($facture) {
                return view('facture.template.colonne-action', compact('facture'));
            })
            ->rawColumns(['actions'])
            ->make(true);
    }


    public function exportFacture(string $id): Response|RedirectResponse
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();

        $montants = $this->calculerMontantFacture($facture, $famille, $enfants);
        $montantcotisation = $montants['montantcotisation'];
        $montantparticipation = $montants['montantparticipation'];
        $montantparticipationSeaska = $montants['montantparticipationSeaska'];
        $montangarderie = $montants['montangarderie'];
        $montanttotal = $montants['montanttotal'];



        $content = view('facture.template.facture-html', compact(
            'facture',
            'famille',
            'enfants',
            'montantcotisation',
            'montantparticipation',
            'montantparticipationSeaska',
            'montangarderie',
            'montanttotal'
        ))->render();
        $htmlInlined = CssInliner::fromHtml($content)->inlineCss()->render();
        if ($facture->etat) {

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlInlined);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();


            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->idFacture . '.pdf"');
        } else {

            return response($htmlInlined, 200)
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->idFacture . '.doc"');




            // $config = HTMLPurifier_Config::createDefault();
            // $purifier = new HTMLPurifier($config);
            // $htmlInlined = mb_convert_encoding($htmlInlined, 'HTML-ENTITIES', 'UTF-8');
            // $htmlInlined = $purifier->purify($htmlInlined);

            // $phpWord = new PhpWord();
            // $section = $phpWord->addSection();
            // Html::addHtml($section, $htmlInlined, false, false);

            // $tempFile = tempnam(sys_get_temp_dir(), 'facture') . '.docx';
            // if (ob_get_length()) {
            //     ob_end_clean();
            // }
            // $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            // $objWriter->save($tempFile);

            // return response()->download($tempFile, 'facture-' . $facture->idFacture . '.docx')->deleteFileAfterSend(true);
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
    private function calculerMontantFacture(Facture $facture, Famille $famille, Collection $enfants): array
    {

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
            'montantcotisation' => $montantcotisation,
            'montantparticipation' => $montantparticipation,
            'montantparticipationSeaska' => $montantparticipationSeaska,
            'montangarderie' => $montangarderie,
            'montanttotal' => $montanttotal,
        ];
    }
}
