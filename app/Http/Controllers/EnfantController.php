<?php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Famille;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnfantController extends Controller
{
    private const ENFANT_NOT_FOUND = 'Enfant non trouvé';

    /**
     * Affiche la liste des enfants.
     */
    public function index(Request $request): View
    {
        $query = Enfant::with(['classe', 'famille', 'famille.utilisateurs']);

        // Recherche globale sur nom, prénom, date de naissance, sexe, famille
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('sexe', 'like', "%{$search}%")
                    ->orWhereDate('dateN', 'like', "%{$search}%")
                    ->orWhereHas('famille', function ($q) use ($search) {
                        $q->where('idFamille', 'like', "%{$search}%")
                            ->orWhereHas('utilisateurs', function ($q) use ($search) {
                                $q->where('nom', 'like', "%{$search}%")
                                    ->orWhere('prenom', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Gestion du tri
        $sortColumn    = $request->get('sort', 'nom');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortColumns = ['nom', 'prenom', 'dateN', 'sexe', 'classe', 'famille'];
        if (! in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'nom';
        }

        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Pour le tri par classe ou famille, on doit faire un join
        if ($sortColumn === 'classe') {
            $query->leftJoin('classe', 'enfant.idClasse', '=', 'classe.idClasse')
                ->select('enfant.*')
                ->orderBy('classe.nom', $sortDirection);
        } elseif ($sortColumn === 'famille') {
            $query->leftJoin('famille', 'enfant.idFamille', '=', 'famille.idFamille')
                ->select('enfant.*')
                ->orderBy('famille.idFamille', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $enfants = $query->paginate(15)->withQueryString();

        return view('admin.enfants.index', compact('enfants', 'sortColumn', 'sortDirection'));
    }

    /**
     * Affiche le formulaire de création d'un enfant.
     */
    public function create(): View
    {
        $classes  = Classe::orderBy('nom')->get();
        $familles = Famille::with('utilisateurs')->orderBy('idFamille')->get();

        return view('admin.enfants.create', compact('classes', 'familles'));
    }

    /**
     * Enregistre un nouvel enfant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'            => 'required|string|max:20',
            'prenom'         => 'required|string|max:150',
            'dateN'          => 'required|date',
            'sexe'           => 'required|string|max:5|in:M,F',
            'NNI'            => 'required|string|regex:/^[0-9]{10}$/',
            'nbFoisGarderie' => 'nullable|integer|min:0',
            'idClasse'       => 'nullable|integer|exists:classe,idClasse',
            'idFamille'      => 'nullable|integer|exists:famille,idFamille',
        ]);

        // Convertir NNI en integer (bigInteger) pour la base de données
        // La colonne NNI a été modifiée en bigInteger pour supporter les 10 chiffres
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
    public function show($id): View | RedirectResponse
    {
        $enfant = Enfant::with(['classe', 'famille.utilisateurs'])->find($id);

        if (! $enfant) {
            return redirect()->route('admin.enfants.index');
        }

        return view('admin.enfants.show', compact('enfant'));
    }

    /**
     * Affiche le formulaire d'édition d'un enfant.
     */
    public function edit($id): View | RedirectResponse
    {
        $enfant = Enfant::find($id);

        if (! $enfant) {
            return redirect()->route('admin.enfants.index');
        }

        $classes  = Classe::orderBy('nom')->get();
        $familles = Famille::with('utilisateurs')->orderBy('idFamille')->get();

        return view('admin.enfants.edit', compact('enfant', 'classes', 'familles'));
    }

    /**
     * Met à jour un enfant.
     */
    public function update(Request $request, $id)
    {
        $enfant = Enfant::find($id);

        if (! $enfant) {
            return redirect()->route('admin.enfants.index');
        }

        $validated = $request->validate([
            'nom'            => 'required|string|max:20',
            'prenom'         => 'required|string|max:150',
            'dateN'          => 'required|date',
            'sexe'           => 'required|string|max:5|in:M,F',
            'NNI'            => 'required|string|regex:/^[0-9]{10}$/',
            'nbFoisGarderie' => 'nullable|integer|min:0',
            'idClasse'       => 'nullable|integer|exists:classe,idClasse',
            'idFamille'      => 'nullable|integer|exists:famille,idFamille',
        ]);

        // Convertir NNI en integer (bigInteger) pour la base de données
        // La colonne NNI a été modifiée en bigInteger pour supporter les 10 chiffres
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

        if (! $enfant) {
            return $this->handleNotFoundResponse();
        }

        $enfant->delete();

        return $this->handleSuccessResponse('Enfant supprimé avec succès', __('enfants.deleted_success'));
    }

    /**
     * Gère la réponse lorsque l'enfant n'est pas trouvé.
     */
    private function handleNotFoundResponse()
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => self::ENFANT_NOT_FOUND], 404);
        }
        return redirect()->route('admin.enfants.index');
    }

    /**
     * Gère la réponse de succès après suppression.
     */
    private function handleSuccessResponse(string $jsonMessage, string $flashMessage)
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => $jsonMessage]);
        }
        return redirect()
            ->route('admin.enfants.index')
            ->with('success', $flashMessage);
    }

}
