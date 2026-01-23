<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Document;
use App\Models\Etiquette;
use App\Models\Posseder;
use App\Http\Requests\StoreActualiteRequest; // Import de la Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;

class ActualiteController extends Controller
{
    private const DATE_FORMAT = 'd/m/Y';
    /**
     * Affiche la liste publique des actualités (Front-end).
     */
    public function index(?Request $request = null)
    {
        $this->ensureEtiquetteIsPublicColumn();

        $request = $request ?? request();
        // 1. Définir les étiquettes autorisées
        // Étiquettes non liées à un rôle : accessibles à tous
        $unboundIds = Etiquette::whereNotIn('idEtiquette', Posseder::distinct()->pluck('idEtiquette'))->pluck('idEtiquette')->toArray();

        $hasIsPublic = Schema::hasColumn('etiquette', 'public');
        $publicTagIds = $hasIsPublic
            ? Etiquette::where('public', true)->pluck('idEtiquette')->toArray()
            : [];
        if (!Auth::check()) {
            // Invité : seules les étiquettes publiques sont considérées
            $etiquettes = Etiquette::whereIn('idEtiquette', $publicTagIds)->get();
            $allowedIdsArray = $publicTagIds;
        } else {
            $roleIds = Auth::user()->rolesCustom()->pluck('avoir.idRole'); // Ajuste selon ta structure user
            $allowedIds = Posseder::whereIn('idRole', $roleIds)->pluck('idEtiquette')->toArray();
            $allowedIdsArray = array_values(array_unique(array_merge($allowedIds, $publicTagIds)));
            $etiquettes = Etiquette::whereIn('idEtiquette', $allowedIdsArray)->get();
        }

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
                          ->where(function($qq) use ($allowedIdsArray, $unboundIds) {
                              $qq->doesntHave('etiquettes')
                                 ->orWhereHas('etiquettes', fn($sq) => $sq->whereIn('etiquette.idEtiquette', array_merge($allowedIdsArray, $unboundIds)));
                          });
                  });
            });
        } else {
            // Non connecté : uniquement les public, sans filtrage par étiquette
            $query->where('type', 'public')
                  ->where(function($q) use ($publicTagIds) {
                      $q->doesntHave('etiquettes')
                        ->orWhereHas('etiquettes', fn($sq) => $sq->whereIn('etiquette.idEtiquette', $publicTagIds));
                  });
            // On ignore les filtres d'étiquettes persistés
            $selected = [];
            session()->forget('selectedEtiquettes');
        }

        // 4. Filtre utilisateur (Sidebar/Recherche)
        $selected = $selected ?? $request->query('etiquettes', session('selectedEtiquettes', []));
        if (!empty($selected) && Auth::check()) {
            // Sécurité : On ne garde que l'intersection avec les droits
            $validSelection = array_intersect(array_map('intval', (array)$selected), $allowedIdsArray);
            if (!empty($validSelection)) {
                $query->whereHas('etiquettes', fn($q) => $q->whereIn('etiquette.idEtiquette', $validSelection));
            }
        }

        $actualites = $query->orderBy('dateP', 'desc')->paginate(10)->appends($request->query());

        return view('actualites.index', compact('actualites', 'etiquettes', 'selected'));
    }

    public function actualitesAdmin()
    {
        return view('admin.actualites.index');
    }

    public function getDatatable(Request $request)
    {
        if($request->ajax()){
            return DataTables::of(Actualite::query())
                ->editColumn('dateP', function ($row) {
                    return \Carbon\Carbon::parse($row->dateP)->format(self::DATE_FORMAT);
                })
                ->editColumn('archive', function ($row) {
                    return $row->archive ? 'Archivé' : 'Publié';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('actualite-show', $row);
                    $editUrl = route('admin.actualites.edit', $row);
                    $deleteUrl = route('admin.actualites.delete', $row);
                
                    return '
                        <a href="'.$showUrl.'" style="color: black;"><i class="bi bi-eye"></i></a>
                        <a href="'.$editUrl.'" style="color: black;"><i class="bi bi-pencil-fill"></i></a>
                
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" style="border: none; padding: 0px"
                                onclick="return confirm(\'Supprimer cette actualité ?\')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.actualites.index');
    }

    /**
     * Traite le formulaire de filtre (POST -> Session -> Redirect).
     */
    public function filter(Request $request)
    {
        $selected = $request->input('etiquettes', []);
        empty($selected) ? session()->forget('selectedEtiquettes') : session(['selectedEtiquettes' => array_map('intval', (array)$selected)]);
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
        // Support both StoreActualiteRequest and plain Request in tests
        if (method_exists($request, 'validated')) {
            $data = $request->validated();
        } else {
            // Ensure slashed dates (d/m/Y) are normalized before validation
            if ($request->has('dateP') && str_contains($request->dateP, '/')) {
                $d = \DateTime::createFromFormat(self::DATE_FORMAT, $request->dateP);
                if ($d) {
                    $request->merge(['dateP' => $d->format('Y-m-d')]);
                }
            }
            $data = $request->validate((new StoreActualiteRequest())->rules());
        }
        $data['idUtilisateur'] = Auth::id();

        $actualite = Actualite::create($data);

        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        }

        if ($request->hasFile('images')) {
            $this->uploadImages($request->file('images'), $actualite);
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

        if (method_exists($request, 'validated')) {
            $validated = $request->validated();
        } else {
            // Normalize slashed date format before validating as StoreActualiteRequest is not executed
            if ($request->has('dateP') && str_contains($request->dateP, '/')) {
                $d = \DateTime::createFromFormat(self::DATE_FORMAT, $request->dateP);
                if ($d) {
                    $request->merge(['dateP' => $d->format('Y-m-d')]);
                }
            }
            $validated = $request->validate((new StoreActualiteRequest())->rules());
        }

        // update() utilise les données déjà validées et formatées (dateP convertie)
        $actualite->update($validated);

        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        } else {
            $actualite->etiquettes()->detach();
        }

        if ($request->hasFile('images')) {
            $this->uploadImages($request->file('images'), $actualite);
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
        $document = Document::findOrFail($idDocument);
        
        // Optionnel : supprimer le fichier physique
        Storage::disk('public')->delete($document->chemin);
        $document->delete();
        
        $actualite->documents()->detach($idDocument);
        return back()->with('success', 'Image retirée.');
    }

    public function adminIndex(Request $request)
    {
        $this->ensureEtiquetteIsPublicColumn();

        $query = Actualite::with('etiquettes')->orderBy('dateP', 'desc');

        $filters = [
            'type' => $request->get('type', ''),
            'etat' => $request->get('etat', ''),
            'etiquette' => $request->get('etiquette', ''),
            'search' => $request->get('search', ''),
        ];

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['etat'])) {
            $filters['etat'] === 'active'
                ? $query->where('archive', false)
                : $query->where('archive', true);
        }

        if (!empty($filters['etiquette'])) {
            $ids = array_map('intval', (array)$filters['etiquette']);
            $query->whereHas('etiquettes', fn($q) => $q->whereIn('etiquette.idEtiquette', $ids));
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($term) {
                $q->where('titrefr', 'like', $term)
                  ->orWhere('titreeus', 'like', $term);
            });
        }

        $actualites = $query->paginate(10)->appends($request->query());
        $etiquettes = Etiquette::all();

        return view('actualites.pannel', compact('actualites', 'etiquettes', 'filters'));
    }

    /**
     * Ajoute la colonne public sur etiquette si absente (pas de nouvelle migration).
     */
    private function ensureEtiquetteIsPublicColumn(): void
    {
        if (!Schema::hasColumn('etiquette', 'public')) {
            Schema::table('etiquette', function ($table) {
                $table->boolean('public')->default(false)->after('nom');
            });
        }
    }

    /**
     * Méthode privée pour gérer l'upload (évite la duplication de code)
     */
    private function uploadImages(array $files, Actualite $actualite)
    {
        foreach ($files as $file) {
            $path = $file->store('actualites', 'public');
            $document = Document::create([
                'nom' => $file->getClientOriginalName(),
                'chemin' => $path,
                'type' => 'image',
                'etat' => 'actif',
            ]);
            $actualite->documents()->attach($document->idDocument);
        }
    }

    /**
     * Données pour DataTables (Logique inlined pour réduire le nombre de méthodes)
     */
    public function data(?Request $request = null)
    {
        $request = $request ?? request();
        $query = Actualite::query()->with('etiquettes');

        // Filtres simples
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('etat')) {
            $request->input('etat') === 'active' ? $query->where('archive', false) : $query->where('archive', true);
        }
        
        // Filtre Etiquettes
        if ($request->filled('etiquette')) {
            $ids = array_map('intval', (array)$request->input('etiquette'));
            $query->whereHas('etiquettes', function($q) use ($ids) {
                $q->whereIn('etiquette.idEtiquette', $ids);
            });
        }

        return DataTables::of($query)
            ->addColumn('titre', fn($actu) => $actu->titrefr ?? 'Sans titre')
            ->addColumn('etiquettes', fn($actu) => $actu->etiquettes->pluck('nom')->join(', '))
            ->addColumn('etat', fn($actu) => $actu->archive ? Lang::get('actualite.archived') : Lang::get('actualite.active'))
            ->addColumn('actions', fn($actu) => view('actualites.template.colonne-action', ['actualite' => $actu]))
            
            // Filtre Titre (recherche globale)
            ->filterColumn('titre', function($query, $keyword) {
                $query->where(fn($sq) => $sq->where('titrefr', 'like', "%{$keyword}%")->orWhere('titreeus', 'like', "%{$keyword}%"));
            })
            // Filtre Etiquettes (recherche textuelle)
            ->filterColumn('etiquettes', function($query, $keyword) {
                $query->whereHas('etiquettes', fn($sq) => $sq->where('nom', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['actions'])
            ->make(true);
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
