<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreActualiteRequest;
use App\Models\Actualite;
use App\Models\Document;
use App\Models\Etiquette;
use App\Models\Posseder; // Import de la Request
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class ActualiteController extends Controller
{
    private const ZIP_PREFIX_IMAGES = 'image_';

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

        $hasIsPublic  = Schema::hasColumn('etiquette', 'public');
        $publicTagIds = $hasIsPublic
            ? Etiquette::where('public', true)->pluck('idEtiquette')->toArray()
            : [];
        $authUser = $request->user() ?? Auth::user();
        if (! $authUser) {
            // Invité : seules les étiquettes publiques sont considérées
            $etiquettes      = Etiquette::whereIn('idEtiquette', $publicTagIds)->get();
            $allowedIdsArray = $publicTagIds;
        } else {
            $roleIds         = $authUser->rolesCustom->pluck('idRole')->toArray();
            $allowedIds      = Posseder::whereIn('idRole', $roleIds)->pluck('idEtiquette')->toArray();
            $allowedIdsArray = array_values(array_unique(array_merge($allowedIds, $publicTagIds)));
            $etiquettes      = Etiquette::whereIn('idEtiquette', $allowedIdsArray)->get();
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
        // Support both StoreActualiteRequest and plain Request in tests
        if (method_exists($request, 'validated')) {
            $data = $request->validated();
        } else {
            // Ensure slashed dates (d/m/Y) are normalized before validation
            if ($request->has('dateP') && str_contains($request->dateP, '/')) {
                $d = \DateTime::createFromFormat('d/m/Y', $request->dateP);
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

    /**
     * Télécharge toutes les images d'une actualité sous forme de ZIP.
     * Nom du ZIP: image_TitreActualité.zip
     */
    public function downloadImagesZip(Actualite $actualite): BinaryFileResponse
    {
        $actualite->loadMissing('documents');

        $images = $actualite->documents->where('type', 'image')->values();
        if ($images->isEmpty()) {
            abort(404, 'Aucune image à télécharger.');
        }

        $titre = $actualite->titrefr ?? $actualite->titreeus ?? ('actualite_' . $actualite->idActualite);
        $base = $this->sanitizeFilename(self::ZIP_PREFIX_IMAGES . $titre);
        $zipFilename = $base . '.zip';

        $tmpPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('actu_images_', true) . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, "Impossible de créer l'archive ZIP.");
        }

        $usedNames = [];
        $i = 1;
        foreach ($images as $doc) {
            if (!Storage::disk('public')->exists($doc->chemin)) {
                continue;
            }

            $path = Storage::disk('public')->path($doc->chemin);
            $ext = pathinfo($doc->chemin, PATHINFO_EXTENSION);

            $name = $doc->nom ?: ('image_' . $i . ($ext ? ('.' . $ext) : ''));
            // Nettoyer et garantir unicité dans le ZIP
            $name = $this->sanitizeFilename($name);
            if ($ext && !str_ends_with(strtolower($name), '.' . strtolower($ext))) {
                $name .= '.' . $ext;
            }
            $candidate = $name;
            $suffix = 2;
            while (isset($usedNames[strtolower($candidate)])) {
                $candidate = pathinfo($name, PATHINFO_FILENAME) . '_' . $suffix . ($ext ? ('.' . $ext) : '');
                $suffix++;
            }
            $usedNames[strtolower($candidate)] = true;

            $zip->addFile($path, $candidate);
            $i++;
        }

        $zip->close();

        return response()
            ->download($tmpPath, $zipFilename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Sert un document (image) d'une actualité via une route Laravel.
     * Permet d'afficher les images même si le lien `public/storage` n'existe pas.
     */
    public function showDocument(Actualite $actualite, Document $document)
    {
        // Vérifie que le document est bien rattaché à cette actualité
        $isAttached = $actualite->documents()
            ->wherePivot('idDocument', $document->getKey())
            ->exists();

        if (! $isAttached) {
            abort(404);
        }

        // On sert uniquement depuis le disque public
        $path = $document->chemin;
        if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            // Laisse le navigateur afficher (pas forcer le download)
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    /**
     * Nettoie une chaîne pour un nom de fichier (ZIP et fichiers internes).
     */
    private function sanitizeFilename(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?? $name;
        $name = preg_replace('/_+/', '_', $name) ?? $name;
        $name = trim($name, '_');

        return $name !== '' ? $name : 'fichier';
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
                $d = \DateTime::createFromFormat('d/m/Y', $request->dateP);
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
        $this->ensureEtiquetteIsPublicColumn();

        $query = Actualite::with('etiquettes')->orderBy('dateP', 'desc');

        $filters = [
            'type'      => $request->get('type', ''),
            'etat'      => $request->get('etat', ''),
            'etiquette' => $request->get('etiquette', ''),
            'search'    => $request->get('search', ''),
        ];

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['etat'])) {
            $filters['etat'] === 'active'
                ? $query->where('archive', false)
                : $query->where('archive', true);
        }

        if (! empty($filters['etiquette'])) {
            $ids = array_map('intval', (array) $filters['etiquette']);
            $query->whereHas('etiquettes', fn($q) => $q->whereIn('etiquette.idEtiquette', $ids));
        }

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
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
        if (! Schema::hasColumn('etiquette', 'public')) {
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
            $path     = $file->store('actualites', 'public');
            $document = Document::create([
                'nom'    => $file->getClientOriginalName(),
                'chemin' => $path,
                'type'   => 'image',
                'etat'   => 'actif',
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
        $query   = Actualite::query()->with('etiquettes');

        // Filtres simples
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('etat')) {
            $request->input('etat') === 'active' ? $query->where('archive', false) : $query->where('archive', true);
        }

        // Filtre Etiquettes
        if ($request->filled('etiquette')) {
            $ids = array_map('intval', (array) $request->input('etiquette'));
            $query->whereHas('etiquettes', function ($q) use ($ids) {
                $q->whereIn('etiquette.idEtiquette', $ids);
            });
        }

        return DataTables::of($query)
            ->addColumn('titre', fn($actu) => $actu->titrefr ?? 'Sans titre')
            ->addColumn('etiquettes', fn($actu) => $actu->etiquettes->pluck('nom')->join(', '))
            ->addColumn('etat', fn($actu) => $actu->archive ? Lang::get('actualite.archived') : Lang::get('actualite.active'))
            ->addColumn('actions', fn($actu) => view('actualites.template.colonne-action', ['actualite' => $actu]))

        // Filtre Titre (recherche globale) — use direct callable to improve testability
            ->filterColumn('titre', [$this, 'filterColumnTitreInline'])
        // Filtre Etiquettes (recherche textuelle) — use direct callable
            ->filterColumn('etiquettes', [$this, 'filterColumnEtiquettesInline'])
        // Register callable wrappers so unit tests can invoke them directly
            ->filterColumn('titre', [$this, 'filterColumnTitreCallback'])
            ->filterColumn('etiquettes', [$this, 'filterColumnEtiquettesCallback'])
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

    /**
     * Inline titre filter extracted to a private method so unit tests can target it.
     */
    private function filterColumnTitreInline($q, $keyword)
    {
        $q->where(fn($sq) => $sq->where('titrefr', 'like', "%{$keyword}%")->orWhere('titreeus', 'like', "%{$keyword}%"));
    }

    /**
     * Inline etiquettes filter extracted to a private method so unit tests can target it.
     */
    private function filterColumnEtiquettesInline($q, $keyword)
    {
        $q->whereHas('etiquettes', fn($sq) => $sq->where('nom', 'like', "%{$keyword}%"));
    }
}
