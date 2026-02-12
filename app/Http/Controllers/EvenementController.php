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
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('idEvenement', $search);
            });
        }

        // Tri dynamique
        $sort = $request->input('sort', 'id_desc');
        $allowedSorts = [
            'id_desc' => ['idEvenement', 'desc'],
            'id_asc' => ['idEvenement', 'asc'],
            'date_desc' => ['start_at', 'desc'],
            'date_asc'  => ['start_at', 'asc'],
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
        $roles = Role::query()->orderBy('name')->get();
        return view('evenements.create', compact('roles'));
    }

    /**
     * Enregistrement d'un événement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'obligatoire' => ['nullable', 'boolean'],

            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],

            'roles' => ['required', 'array', 'min:1', 'max:50'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        // Sanitization contre XSS
        $titre = strip_tags($validated['titre']);
        $description = strip_tags($validated['description']);

        $evenement = Evenement::create([
            'titre' => $titre,
            'description' => $description,
            'obligatoire' => (bool)($validated['obligatoire'] ?? false),

            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
        ]);

        $evenement->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('evenements.index')
            ->with('success', 'Événement créé avec succès');
    }

    /**
     * Afficher un événement
     */
    public function show($id)
    {
        $evenement = Evenement::with('roles')->findOrFail($id);
        return view('evenements.show', compact('evenement'));
    }

    /**
     * Formulaire d’édition
     */
    public function edit($id)
    {
        $evenement = Evenement::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('evenements.edit', compact('evenement', 'roles'));
    }

    /**
     * Mise à jour d'un événement
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'obligatoire' => ['nullable', 'boolean'],

            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],

            'roles' => ['required', 'array', 'min:1', 'max:50'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        $evenement = Evenement::findOrFail($id);

        // Sanitization contre XSS
        $titre = strip_tags($validated['titre']);
        $description = strip_tags($validated['description']);

        $evenement->update([
            'titre' => $titre,
            'description' => $description,
            'obligatoire' => (bool)($validated['obligatoire'] ?? false),

            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
        ]);

        $evenement->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('evenements.index')
            ->with('success', 'Événement mis à jour avec succès');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);
        $evenement->roles()->detach();
        $evenement->delete();

        return redirect()->route('evenements.index')
            ->with('success', 'Événement supprimé avec succès');
    }
}
