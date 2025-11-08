<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enfant;
class EnfantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function create()
    {
        return view('enfants.create');
    }
   public function store(Request $request)
{
    // Création de l'enfant directement, sans validation
    $enfant = Enfant::create([
        'idEnfant'  => $request->idEnfant,
        'nom'       => $request->nom,
        'prenom'    => $request->prenom,
        'dateN'     => $request->dateN,
        'sexe'      => $request->sexe,
        'NNI'       => $request->NNI,
        'idClasse'  => $request->idClasse,
        'idFamille' => $request->idFamille,
    ]);

    return response()->json($enfant, 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
