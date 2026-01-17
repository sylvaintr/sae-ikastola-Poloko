<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Enfant;
use App\Models\Classe;
use App\Models\Famille;

class EnfantController extends Controller
{
    private const ENFANT_NOT_FOUND = 'Enfant non trouvé';

    /**
     * Affiche la liste des enfants.
     */
    public function index(Request $request): View
    {
        $query = Enfant::with(['classe', 'famille']);

        // Filtres
        $filters = [];
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            });
            $filters['search'] = $search;
        }

        if ($request->filled('sexe')) {
            $sexe = $request->input('sexe');
            $query->where('sexe', $sexe);
            $filters['sexe'] = $sexe;
        }

        if ($request->filled('idClasse')) {
            $idClasse = $request->input('idClasse');
            $query->where('idClasse', $idClasse);
            $filters['idClasse'] = $idClasse;
        }

        if ($request->filled('idFamille')) {
            $idFamille = $request->input('idFamille');
            if ($idFamille === 'null') {
                $query->whereNull('idFamille');
            } else {
                $query->where('idFamille', $idFamille);
            }
            $filters['idFamille'] = $idFamille;
        }

        $enfants = $query->orderBy('nom')->orderBy('prenom')->paginate(15)->appends($request->query());
        $classes = Classe::orderBy('nom')->get();
        $familles = Famille::with('utilisateurs')->orderBy('idFamille')->get();

        return view('admin.enfants.index', compact('enfants', 'filters', 'classes', 'familles'));
    }

    /**
     * Affiche le formulaire de création d'un enfant.
     */
    public function create(): View
    {
        $classes = Classe::orderBy('nom')->get();
        $familles = Famille::with('utilisateurs')->orderBy('idFamille')->get();

        return view('admin.enfants.create', compact('classes', 'familles'));
    }

    /**
     * Enregistre un nouvel enfant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:20',
            'prenom' => 'required|string|max:150',
            'dateN' => 'required|date',
            'sexe' => 'nullable|string|max:5|in:M,F',
            'NNI' => 'required|string|regex:/^[0-9]{10}$/',
            'nbFoisGarderie' => 'nullable|integer|min:0',
            'idClasse' => 'nullable|integer|exists:classe,idClasse',
            'idFamille' => 'nullable|integer|exists:famille,idFamille',
        ]);

        // Convertir NNI en integer pour la base de données
        $validated['NNI'] = (int) $validated['NNI'];

        // Valeurs par défaut
        $validated['nbFoisGarderie'] = $validated['nbFoisGarderie'] ?? 0;
        
        // idFamille et idClasse peuvent être null
        if (empty($validated['idFamille'])) {
            $validated['idFamille'] = null;
        }
        if (empty($validated['idClasse'])) {
            $validated['idClasse'] = null;
        }

        Enfant::create($validated);

        return redirect()
            ->route('admin.enfants.index')
            ->with('success', __('enfants.created_success'));
    }

    /**
     * Affiche les détails d'un enfant.
     */
    public function show($id): View
    {
        $enfant = Enfant::with(['classe', 'famille.utilisateurs'])->find($id);

        if (!$enfant) {
            return redirect()->route('admin.enfants.index');
        }

        return view('admin.enfants.show', compact('enfant'));
    }

    /**
     * Affiche le formulaire d'édition d'un enfant.
     */
    public function edit($id): View
    {
        $enfant = Enfant::find($id);

        if (!$enfant) {
            return redirect()->route('admin.enfants.index');
        }

        $classes = Classe::orderBy('nom')->get();
        $familles = Famille::with('utilisateurs')->orderBy('idFamille')->get();

        return view('admin.enfants.edit', compact('enfant', 'classes', 'familles'));
    }

    /**
     * Met à jour un enfant.
     */
    public function update(Request $request, $id)
    {
        $enfant = Enfant::find($id);

        if (!$enfant) {
            return redirect()->route('admin.enfants.index');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:20',
            'prenom' => 'required|string|max:150',
            'dateN' => 'required|date',
            'sexe' => 'nullable|string|max:5|in:M,F',
            'NNI' => 'required|string|regex:/^[0-9]{10}$/',
            'nbFoisGarderie' => 'nullable|integer|min:0',
            'idClasse' => 'nullable|integer|exists:classe,idClasse',
            'idFamille' => 'nullable|integer|exists:famille,idFamille',
        ]);

        // Convertir NNI en integer pour la base de données
        $validated['NNI'] = (int) $validated['NNI'];

        // idFamille et idClasse peuvent être null
        if (empty($validated['idFamille'])) {
            $validated['idFamille'] = null;
        }
        if (empty($validated['idClasse'])) {
            $validated['idClasse'] = null;
        }

        $enfant->update($validated);

        return redirect()
            ->route('admin.enfants.index')
            ->with('success', __('enfants.updated_success'));
    }

    /**
     * Supprime un enfant.
     */
    public function destroy($id)
    {
        $enfant = Enfant::find($id);

        if (!$enfant) {
            if (request()->wantsJson()) {
                return response()->json(['message' => self::ENFANT_NOT_FOUND], 404);
            }
            return redirect()->route('admin.enfants.index');
        }

        $enfant->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Enfant supprimé avec succès']);
        }

        return redirect()
            ->route('admin.enfants.index')
            ->with('success', __('enfants.deleted_success'));
    }
}

