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
use App\Models\Utilisateur;

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

        $facture = Facture::find($id);
        if ($facture === null) {
            return redirect()->route('facture.index')->with('error', 'facture.inexistante');
        }
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille);


        return view('facture.show', [
            'facture' => $facture,
            'famille' => $famille,
            'enfants' => $enfants,
            // 'nomFamille' => $nomFamille,

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
                $buttons = '<a href="' . route('facture.show', $facture->idFacture) . '" class="text-decoration-none text-black" ><i class="bi bi-eye me-3"></i></a>';
                if (!$facture->etat) {
                    $buttons .= '<i class="bi bi-pencil-fill me-3"></i>';
                    $buttons .= '<a href="' . route('facture.valider', $facture->idFacture) . '" class="text-decoration-none text-black" ><i class="bi bi-check-lg me-3"></i></a>';
                } else {
                    $buttons .= '<a href="' . route('facture.envoyer', $facture->idFacture) . '" class="text-decoration-none text-black" ><i class="bi bi-send-fill me-3"></i></a>';
                }
                $buttons .= '<a href="' . route('facture.export', $facture->idFacture) . '" class="text-decoration-none text-black" ><i class="bi bi-download me-3"></i></a>';
                return $buttons;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }


    public function exportFacture(string $id): Response
    {
        $facture = Facture::findOrFail($id);
        $famille = Famille::find($facture->idFamille);
        $enfants = Enfant::where('idFamille', $famille->idFamille);
        $content = view('facture.template.facture-html', compact('facture', 'famille', 'enfants'))->render();
        if ($facture->etat) {
            $reponse = response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->idFacture . '.pdf"');
        } else {
            $reponse = response($content)
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->idFacture . '.doc"');
        }
        return $reponse;
    }



    public function envoyerFacture(string $id): RedirectResponse
    {
        $facture = Facture::findOrFail($id);
        $client = Utilisateur::find($facture->idUtilisateur);
        if ($facture->etat) {

            Mail::to($client->email)->send(new FactureMail($facture));
            return redirect()->route('facture.index')->with('success', 'facture.envoiersuccess');
        } else {
            return redirect()->route('facture.index')->with('error', 'facture.envoiererror');
        }
    }

    public function validerFacture(string $id): RedirectResponse
    {
        try {
            $facture = Facture::findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('facture.index')->with('error', 'facture.inexistante');
        }
        $facture->etat = true;
        $facture->save();

        return redirect()->route('facture.index')->with('success', 'facture.validersuccess');
    }
}
