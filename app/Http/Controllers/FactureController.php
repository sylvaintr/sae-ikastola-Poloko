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
use Pelago\Emogrifier\CssInliner;

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
    public function show(string $id)
    {

        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();

        // calcul du montant  cotisation
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


    public function exportFacture(string $id): Response
    {
        $facture = Facture::find($id ?? null);
        if ($facture === null) {
            return response(redirect()->route('admin.facture.index')->with('error', 'facture.inexistante'));
        }
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille)->get();

        // calcul du montant  cotisation
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


        $content = view('facture.template.facture-html', compact('facture', 'famille', 'enfants', 'montantcotisation', 'montantparticipation', 'montantparticipationSeaska', 'montangarderie', 'montanttotal'))->render();
        $htmlInlined = CssInliner::fromHtml($content)->inlineCss()->render();
        if ($facture->etat) {
            $reponse = response($htmlInlined)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->idFacture . '.pdf"');
        } else {
            $reponse = response($htmlInlined)
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->idFacture . '.doc"');
        }
        return $reponse;
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
        try {
            $facture = Facture::findOrFail($id ?? null);
        } catch (\Exception $e) {
            return redirect()->route('admin.facture.index')->with('error', 'facture.inexistante');
        }
        $facture->etat = true;
        $facture->save();

        return redirect()->route('admin.facture.index')->with('success', 'facture.validersuccess');
    }
}
