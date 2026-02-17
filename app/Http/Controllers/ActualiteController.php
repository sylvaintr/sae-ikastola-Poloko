<?php
namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Document;
use App\Models\Etiquette;
use App\Services\ActualiteDataTableService;
use App\Services\ActualiteEtiquetteService;
use App\Services\ActualiteFilterService;
use App\Services\ActualiteImageService;
use App\Services\ActualiteValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActualiteController extends Controller
{
    private ActualiteEtiquetteService $etiquetteService;
    private ActualiteFilterService $filterService;
    private ActualiteValidationService $validationService;
    private ActualiteImageService $imageService;
    private ActualiteDataTableService $dataTableService;

    public function __construct(
        ActualiteEtiquetteService $etiquetteService,
        ActualiteFilterService $filterService,
        ActualiteValidationService $validationService,
        ActualiteImageService $imageService,
        ActualiteDataTableService $dataTableService
    ) {
        $this->etiquetteService = $etiquetteService;
        $this->filterService = $filterService;
        $this->validationService = $validationService;
        $this->imageService = $imageService;
        $this->dataTableService = $dataTableService;
    }

    /**
     * Affiche la liste publique des actualités (Front-end).
     */
    public function index(?Request $request = null)
    {
        $this->etiquetteService->ensurePublicColumn();

        $request = $request ?? request();
        $etiquetteData = $this->etiquetteService->resolveAllowedEtiquettes($request);
        $etiquettes = $etiquetteData['etiquettes'];
        $allowedIdsArray = $etiquetteData['allowedIds'];
        $publicTagIds = $etiquetteData['publicTagIds'];
        $unboundIds = $etiquetteData['unboundIds'];

        // 2. Construire la requête
        $query = Actualite::with(['etiquettes', 'documents'])
            ->where('archive', false)
            ->where('dateP', '<=', now());

        // 3. Types visibles :
        //    - Toujours les "public"
        //    - Les "private" uniquement si connecté
        if (Auth::check()) {
            // Public visibles sans restriction, private filtrées par étiquettes autorisées
            $query->where(function ($q) use ($allowedIdsArray, $unboundIds) {
                // Cas 1 : public => toujours visible
                $q->where('type', 'public')
                // Cas 2 : private + étiquettes autorisées (ou aucune étiquette)
                    ->orWhere(function ($sq) use ($allowedIdsArray, $unboundIds) {
                        $sq->where('type', 'private')
                            ->where(function ($qq) use ($allowedIdsArray, $unboundIds) {
                                $qq->doesntHave('etiquettes')
                                    ->orWhereHas('etiquettes', fn($sq) => $sq->whereIn('etiquette.idEtiquette', array_merge($allowedIdsArray, $unboundIds)));
                            });
                    });
            });
        } else {
            // Non connecté : uniquement les public, sans filtrage par étiquette
            $query->where('type', 'public')
                ->where(function ($q) use ($publicTagIds) {
                    $q->doesntHave('etiquettes')
                        ->orWhereHas('etiquettes', fn($sq) => $sq->whereIn('etiquette.idEtiquette', $publicTagIds));
                });
            // On ignore les filtres d'étiquettes persistés
            $selected = [];
            session()->forget('selectedEtiquettes');
        }

        // 4. Filtre utilisateur (Sidebar/Recherche)
        $selected = $selected ?? $request->query('etiquettes', session('selectedEtiquettes', []));
        if (! empty($selected) && Auth::check()) {
            // Sécurité : On ne garde que l'intersection avec les droits
            $validSelection = array_intersect(array_map('intval', (array) $selected), $allowedIdsArray);
            if (! empty($validSelection)) {
                $query->whereHas('etiquettes', fn($q) => $q->whereIn('etiquette.idEtiquette', $validSelection));
            }
        }

        $actualites = $query->orderBy('dateP', 'desc')->paginate(10)->appends($request->query());

        return view('actualites.index', compact('actualites', 'etiquettes', 'selected'));
    }

    /**
     * Traite le formulaire de filtre (POST -> Session -> Redirect).
     */
    public function filter(Request $request)
    {
        $selected = $request->input('etiquettes', []);
        empty($selected) ? session()->forget('selectedEtiquettes') : session(['selectedEtiquettes' => array_map('intval', (array) $selected)]);
        return redirect()->route('home');
    }

    public function create()
    {
        $etiquettes = Etiquette::all();
        return view('actualites.create', compact('etiquettes'));
    }

    /**
     * Enregistre une actualité.
     * Utilise StoreActualiteRequest pour la validation et la conversion de date.
     */
    public function store(Request $request)
    {
        $data = $this->validationService->validateRequest($request);
        $data['idUtilisateur'] = Auth::id();

        $actualite = Actualite::create($data);

        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        }

        if ($request->hasFile('images')) {
            $this->imageService->uploadImages($request->file('images'), $actualite);
        }

        return redirect()->route('home')->with('success', 'Actualité créée avec succès.');
    }

    public function show($id)
    {
        $actualite = Actualite::with(['etiquettes', 'documents', 'utilisateur'])->findOrFail($id);
        return view('actualites.show', compact('actualite'));
    }

    public function edit($id)
    {
        try {
            $actualite = Actualite::with(['etiquettes', 'documents'])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.actualites.index')->with('error', __('actualite.not_found'));
        }
        $etiquettes = Etiquette::all();
        return view('actualites.edit', compact('actualite', 'etiquettes'));
    }

    /**
     * Met à jour l'actualité.
     * On réutilise StoreActualiteRequest car les règles sont identiques.
     */
    public function update(Request $request, $id)
    {
        try {
            $actualite = Actualite::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', __('actualite.not_found'));
        }

        $validated = $this->validationService->validateRequest($request);

        // update() utilise les données déjà validées et formatées (dateP convertie)
        $actualite->update($validated);

        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        } else {
            $actualite->etiquettes()->detach();
        }

        if ($request->hasFile('images')) {
            $this->imageService->uploadImages($request->file('images'), $actualite);
        }

        return redirect()->route('actualites.show', $id)->with('success', 'Actualité mise à jour.');
    }

    public function destroy($id)
    {
        $actualite = Actualite::findOrFail($id);
        $actualite->etiquettes()->detach();
        foreach ($actualite->documents as $document) {
            Storage::disk('public')->delete($document->chemin);
            $document->delete();
        }
        $actualite->documents()->detach();
        $actualite->delete();

        return redirect()->route('admin.actualites.index')->with('success', 'Supprimée.');
    }

    public function detachDocument($idActualite, $idDocument)
    {
        $actualite = Actualite::findOrFail($idActualite);
        $document  = Document::findOrFail($idDocument);

        // Optionnel : supprimer le fichier physique
        Storage::disk('public')->delete($document->chemin);
        $document->delete();

        $actualite->documents()->detach($idDocument);
        return back()->with('success', 'Image retirée.');
    }

    /**
     * Duplique une actualité avec ses étiquettes et documents.
     */
    public function duplicate($id)
    {
        $original = Actualite::with(['etiquettes', 'documents'])->findOrFail($id);

        // Empêcher la duplication d'une actualité archivée
        if ($original->archive) {
            return redirect()->route('admin.actualites.index')
                ->with('error', __('actualite.cannot_duplicate_archived'));
        }

        // Créer une nouvelle actualité avec les mêmes données (sauf idActualite)
        $duplicate = Actualite::create([
            'titrefr' => $original->titrefr ? ($original->titrefr . ' (Copie)') : null,
            'titreeus' => $original->titreeus ? ($original->titreeus . ' (Kopia)') : null,
            'descriptionfr' => $original->descriptionfr,
            'descriptioneus' => $original->descriptioneus,
            'contenufr' => $original->contenufr,
            'contenueus' => $original->contenueus,
            'type' => $original->type,
            'dateP' => now(),
            'archive' => false,
            'lien' => $original->lien,
            'idUtilisateur' => Auth::id(),
        ]);

        // Dupliquer les étiquettes
        if ($original->etiquettes->isNotEmpty()) {
            $duplicate->etiquettes()->sync($original->etiquettes->pluck('idEtiquette')->toArray());
        }

        // Dupliquer les documents (attacher les mêmes documents, pas de copie physique)
        if ($original->documents->isNotEmpty()) {
            $duplicate->documents()->sync($original->documents->pluck('idDocument')->toArray());
        }

        return redirect()->route('admin.actualites.edit', $duplicate->idActualite)
            ->with('success', __('actualite.duplicated_success'));
    }

    public function adminIndex(Request $request)
    {
        $this->etiquetteService->ensurePublicColumn();

        $query = Actualite::with('etiquettes')->orderBy('dateP', 'desc');
        $filters = $this->filterService->extractFilters($request);
        $this->filterService->applyFilters($query, $filters);

        $actualites = $query->paginate(10)->appends($request->query());
        $etiquettes = Etiquette::all();

        return view('actualites.pannel', compact('actualites', 'etiquettes', 'filters'));
    }


    /**
     * Données pour DataTables.
     */
    public function data(?Request $request = null)
    {
        return $this->dataTableService->buildDataTable(
            $request,
            [$this->dataTableService, 'filterColumnTitreInline'],
            [$this->dataTableService, 'filterColumnEtiquettesInline']
        );
    }


    // Delegates unknown helper calls to a dedicated helper class so the
    // controller stays small (helps static analyzers enforce method limits).
    private ?\App\Http\Controllers\ActualiteHelpers $adtHelpers = null;

    private function actualiteHelpers()
    {
        if ($this->adtHelpers === null) {
            $this->adtHelpers = new \App\Http\Controllers\ActualiteHelpers();
        }
        return $this->adtHelpers;
    }

    public function __call($method, $args)
    {
        $helpers = $this->actualiteHelpers();
        if (method_exists($helpers, $method)) {
            return $helpers->{$method}(...$args);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

}
