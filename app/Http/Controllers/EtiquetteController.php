<?php

namespace App\Http\Controllers;

use App\Models\Etiquette;
use App\Models\Role;
use Illuminate\Http\Request;


class EtiquetteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $etiquettes = Etiquette::all();
        return view('etiquettes.index', compact('etiquettes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('etiquettes.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'nom' => 'required|string|max:50',
                'roles' => ['nullable', 'array'],
                'roles.*' => ['exists:role,idRole'],
            ]
        );

        $etiquette = Etiquette::create(['nom' => $validated['nom']]);
        $rolesToSync = [];
        $roles = $validated['roles'] ?? [];
        foreach ($roles as $roleId) {
            $rolesToSync[$roleId] = [];
        }
        $etiquette->roles()->sync($rolesToSync);


        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteCreee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $idEtiquette)
    {
        try {
            $etiquette = Etiquette::findOrFail($idEtiquette);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.etiquettes.index')->with('error', __('etiquette.errorEtiquetteNonTrouvee'));
        }
        $etiquette->load('roles');
        $roles = Role::all();
        return view('etiquettes.edit', compact('etiquette', 'roles'));
    }

    /**
     * methode pour mettre a jour une etiquette
     * @param Request $request parameters du formulaire
     * @param Etiquette $etiquette etiquette a mettre a jour
     * @return \Illuminate\Http\RedirectResponse redirection vers la liste des etiquettes avec un message de succes ou d'erreur
     */
    public function update(Request $request, Etiquette $etiquette)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:role,idRole'],
        ]);

        $rolesToSync = [];
        $roles = $validated['roles'] ?? [];
        foreach ($roles as $roleId) {
            $rolesToSync[$roleId] = [];
        }
        $etiquette->roles()->sync($rolesToSync);

        $etiquette->update(['nom' => $validated['nom']]);
        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteMiseAJour'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Etiquette $etiquette)
    {
        $etiquette->delete();
        return redirect()->route('admin.etiquettes.index')->with('success', __('etiquette.successEtiquetteSupprimee'));
    }
}
