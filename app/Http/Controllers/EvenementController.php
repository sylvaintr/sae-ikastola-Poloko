<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Role;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    /**
     * Afficher tous les événements
     */
   public function index(Request $request)
{
    $query = Evenement::query();

    // Optionnel : recherche par titre ou ID
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('titre', 'like', "%{$search}%")
              ->orWhere('request_id', 'like', "%{$search}%")
              ->orWhere('cible', 'like', "%{$search}%");
        });
    }

    // Utilise paginate() pour avoir un LengthAwarePaginator
    $evenements = $query->orderByDesc('dateE')
                        ->paginate(10) // nombre par page
                        ->withQueryString(); // conserve les paramètres de recherche

    return view('evenements.index', compact('evenements'));
}


    /**
     * Formulaire de création
     */
    public function create()
    {
        $roles = Role::all();
        return view('evenements.create', compact('roles'));
    }

    /**
     * Enregistrement d'un événement
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'obligatoire' => 'required|boolean',
            'dateE' => 'required|date',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:role,idRole',
        ]);

        $evenement = Evenement::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'obligatoire' => $request->obligatoire,
            'dateE' => $request->dateE,
        ]);

        // Attacher les rôles si fournis
        if ($request->filled('roles')) {
            $evenement->roles()->sync($request->input('roles', []));
        }

        return redirect()->route('evenements.index')
                         ->with('success', 'Événement créé avec succès');
    }

    /**
     * Afficher un événement
     */
    public function show($id)
    {
        $evenement = Evenement::findOrFail($id);
        return view('evenements.show', compact('evenement'));
    }

    /**
     * Formulaire d’édition
     */
    public function edit($id)
    {
        $evenement = Evenement::findOrFail($id);
        $roles = Role::all();
        return view('evenements.edit', compact('evenement', 'roles'));
    }

    /**
     * Mise à jour d'un événement
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'obligatoire' => 'required|boolean',
            'dateE' => 'required|date',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:role,idRole',
        ]);

        $evenement = Evenement::findOrFail($id);

        $evenement->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'obligatoire' => $request->obligatoire,
            'dateE' => $request->dateE,
        ]);

        // Synchroniser les rôles
        $evenement->roles()->sync($request->input('roles', []));

        return redirect()->route('evenements.index')
                         ->with('success', 'Événement mis à jour avec succès');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);
        $evenement->delete();

        return redirect()->route('evenements.index')
                         ->with('success', 'Événement supprimé avec succès');
    }
}
