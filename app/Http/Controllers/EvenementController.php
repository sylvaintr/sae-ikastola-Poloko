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

        // Recherche par titre ou ID
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('request_id', 'like', "%{$search}%")
                  ->orWhere('cible', 'like', "%{$search}%");
            });
        }

        // Tri dynamique
        $sort = $request->input('sort', 'id_desc');
        $allowedSorts = [
            'id_desc' => ['idEvenement', 'desc'],
            'id_asc' => ['idEvenement', 'asc'],
            'date_desc' => ['dateE', 'desc'],
            'date_asc' => ['dateE', 'asc'],
        ];

        if (! array_key_exists($sort, $allowedSorts)) {
            $sort = 'id_desc';
        }

        [$column, $direction] = $allowedSorts[$sort];

        $evenements = $query->orderBy($column, $direction)
                            ->paginate(10)
                            ->withQueryString();

        return view('evenements.index', compact('evenements', 'sort'));
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
            'obligatoire' => 'nullable|boolean',
            'dateE' => 'required|date',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:role,idRole',
        ]);

        $evenement = Evenement::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'obligatoire' => $request->boolean('obligatoire'),
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
            'obligatoire' => 'nullable|boolean',
            'dateE' => 'required|date',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:role,idRole',
        ]);

        $evenement = Evenement::findOrFail($id);

        $evenement->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'obligatoire' => $request->boolean('obligatoire'),
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
