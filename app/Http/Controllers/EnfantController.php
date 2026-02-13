<?php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Famille;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnfantController extends Controller
{
    private const ENFANT_NOT_FOUND = 'Enfant non trouvé';

    /**
     * Methode pour afficher la liste des enfants avec pagination, recherche et tri
     * @param Request $request la requête HTTP contenant les paramètres de recherche et de tri
     * @return View la vue affichant la liste des enfants avec les résultats de la recherche et du tri appliqués
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
     * Methode pour afficher le formulaire de création d'un enfant
     * @return View la vue affichant le formulaire de création d'un enfant avec les listes des classes et des familles disponibles pour l'association de l'enfant
     */
    public function create(): View
    {
        $classes  = Classe::orderBy('nom')->get();
        $familles = Famille::with('utilisateurs')->orderBy('idFamille')->get();

        return view('admin.enfants.create', compact('classes', 'familles'));
    }

    /**
     * Methode pour stocker un nouvel enfant dans la base de données
     * @param Request $request la requête HTTP contenant les données du formulaire de création d'un enfant
     * @return RedirectResponse la réponse de redirection vers la liste des enfants avec un message de succès indiquant que l'enfant a été créé avec succès, ou une redirection vers le formulaire de création avec les erreurs de validation si les données du formulaire ne sont pas valides
     */
    public function store(Request $request): RedirectResponse
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
     * Methode pour afficher les détails d'un enfant
     * @param int $id l'identifiant de l'enfant à afficher
     * @return View | RedirectResponse la vue affichant les détails de l'enfant, ou une redirection vers la liste des enfants si l'enfant n'est pas trouvé
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
     * Methode pour afficher le formulaire d'édition d'un enfant
     * @param int $id l'identifiant de l'enfant à éditer
     * @return View | RedirectResponse la vue affichant le formulaire d'édition de l'enfant, ou une redirection vers la liste des enfants si l'enfant n'est pas trouvé
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
     * Methode pour mettre à jour un enfant dans la base de données
     * @param Request $request la requête HTTP contenant les données du formulaire d'édition d'un enfant
     * @param int $id l'identifiant de l'enfant à mettre à jour
     * @return RedirectResponse la réponse de redirection vers la liste des enfants avec un message de succès indiquant que l'enfant a été mis à jour avec succès, ou une redirection vers le formulaire d'édition avec les erreurs de validation si les données du formulaire ne sont pas valides, ou une redirection vers la liste des enfants si l'enfant n'est pas trouvé
     */
    public function update(Request $request, $id): RedirectResponse
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
     * Methode pour supprimer un enfant de la base de données
     * @param int $id l'identifiant de l'enfant à supprimer
     * @return JsonResponse | RedirectResponse la réponse JSON indiquant le résultat de l'opération de suppression si la requête attend une réponse JSON, ou une redirection vers la liste des enfants avec un message de succès indiquant que l'enfant a été supprimé avec succès si la requête n'attend pas une réponse JSON, ou une redirection vers la liste des enfants si l'enfant n'est pas trouvé
     */
    public function destroy($id): JsonResponse | RedirectResponse
    {
        $enfant = Enfant::find($id);

        if (! $enfant) {
            return $this->handleNotFoundResponse();
        }

        $enfant->delete();

        return $this->handleSuccessResponse('Enfant supprimé avec succès', __('enfants.deleted_success'));
    }

    /**
     * Methode pour envoyer ou rediriger une réponse indiquant que l'enfant n'a pas été trouvé
     * si la requête attend une réponse JSON, retourne une réponse JSON avec un message d'erreur et un code de statut 404, sinon redirige vers la liste des enfants avec un message d'erreur
     * @return JsonResponse|RedirectResponse la réponse JSON ou la redirection vers la liste des enfants selon le type de la requête
     */
    private function handleNotFoundResponse(): JsonResponse | RedirectResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => self::ENFANT_NOT_FOUND], 404);
        }
        return redirect()->route('admin.enfants.index');
    }

    /**
     * Methode pour envoyer ou rediriger une réponse indiquant que l'opération a été effectuée avec succès
     * si la requête attend une réponse JSON, retourne une réponse JSON avec un message de succès, sinon redirige vers la liste des enfants avec un message de succès
     * @param string $jsonMessage le message de succès à inclure dans la réponse JSON si la requête attend une réponse JSON
     * @param string $flashMessage le message de succès à inclure dans le flash message de la redirection si la requête n'attend pas une réponse JSON
     * @return JsonResponse|RedirectResponse la réponse JSON ou la redirection vers la liste des enfants selon le type de la requête
     */
    private function handleSuccessResponse(string $jsonMessage, string $flashMessage): JsonResponse | RedirectResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => $jsonMessage]);
        }
        return redirect()
            ->route('admin.enfants.index')
            ->with('success', $flashMessage);
    }

}
