<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Document;
use App\Models\Etiquette;
use App\Models\Posseder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class ActualiteController extends Controller
{
    /**
     * Affiche la liste des actualités.
     */
    public function index(Request $request)
    {
        // Base query: not archived, published date <= now
        $query = Actualite::with(['etiquettes', 'documents'])
            ->where('archive', false)
            ->where('dateP', '<=', now());

        // Types: always include 'public', include 'private' when authenticated
        $types = ['public'];
        if (Auth::check()) {
            $types[] = 'private';
        }
        $query->whereIn('type', $types);

        // Filter by etiquettes if provided (expects array of idEtiquette)
        // Prefer query string (explicit GET), otherwise fall back to session-stored filter
        $selectedEtiquettes = $request->query('etiquettes', session('selectedEtiquettes', []));
        if (!empty($selectedEtiquettes)) {
            $query->whereHas('etiquettes', function ($q) use ($selectedEtiquettes) {
                // Qualify column with table name to avoid ambiguous column errors when joining pivot tables
                $table = $q->getModel()->getTable();
                $q->whereIn($table . '.idEtiquette', $selectedEtiquettes);
            });
        }

        // Pagination (preserve query string so filters remain on pagination links)
        $actualites = $query->orderBy('dateP', 'desc')->paginate(10)->withQueryString();

        // Etiquettes list (same logic as before)
        if (!Auth::check()) {
            $test = Posseder::all()->pluck('idEtiquette')->toArray();
            $etiquettes = Etiquette::all()->whereNotIn('idEtiquette', $test);
        } else {
            $etiquettes = Etiquette::all();
        }

        return view('actualites.index', compact('actualites', 'etiquettes', 'selectedEtiquettes'));
    }

    /**
     * Receives POST filter submissions, stores selected etiquettes in session and redirects to home (GET).
     */
    public function filter(Request $request)
    {
        $selected = $request->input('etiquettes', []);

        // If no etiquettes selected, clear the filter from session to show all
        if (empty($selected)) {
            session()->forget('selectedEtiquettes');
        } else {
            // Ensure values are integers
            $selected = array_map('intval', (array) $selected);
            session(['selectedEtiquettes' => $selected]);
        }

        return redirect()->route('home');
    }

    /**
     * Affiche le formulaire de création.
     */
    public function create()
    {
        $etiquettes = Etiquette::all();
        return view('actualites.create', compact('etiquettes'));
    }

    /**
     * Enregistre une nouvelle actualité.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Champs Communs
            'type' => 'required|string',
            'dateP' => 'required|date',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            // Champs Français
            'titrefr' => 'nullable|string|max:30',
            'descriptionfr' => 'required|string|max:100',
            'contenufr' => 'required|string',

            // Champs Basques
            'titreeus' => 'nullable|string|max:30',
            'descriptioneus' => 'required|string|max:100',
            'contenueus' => 'required|string',

        ]);


        $actualite = new Actualite([
            'titrefr' => $request->titrefr,
            'titreeus' => $request->titreeus,
            'descriptionfr' => $request->descriptionfr,
            'descriptioneus' => $request->descriptioneus,
            'contenufr' => $request->contenufr,
            'contenueus' => $request->contenueus,
            'type' => $request->type,
            'dateP' => $request->dateP,
            'lien' => $request->lien,
            'idUtilisateur' => Auth::id(),
        ]);

        
        // 2. Gestion des Étiquettes (Relation Pivot)
        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        }
        
        $actualite->save();

        // 3. Gestion des Images (Création de Documents et liaison)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Upload du fichier
                $path = $file->store('actualites', 'public');

                // Génération ID Document


                // Création du Document
                $document = new Document();
                $document->nom = $file->getClientOriginalName();
                $document->chemin = $path;
                $document->type = 'image';
                $document->etat = 'actif';
                $document->save();

                // Liaison Pivot (Actualite <-> Document)
                $actualite->documents()->attach($document->idDocument);
            }
        }

        return redirect()->route('home')->with('success', 'Actualité créée avec succès.');
    }

    /**
     * Affiche une actualité spécifique.
     */
    public function show($id)
    {
        $actualite = Actualite::with(['etiquettes', 'documents', 'utilisateur'])->findOrFail($id);
        return view('actualites.show', compact('actualite'));
    }

    /**
     * Affiche le formulaire d'édition.
     */
    public function edit($id): View|RedirectResponse
    {
        try {
            $actualite = Actualite::with(['etiquettes', 'documents'])->findOrFail($id);
            $etiquettes = Etiquette::all();
            return view('actualites.edit', compact('actualite', 'etiquettes'));
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Actualité non trouvée.');
        }
    }

    /**
     * Met à jour l'actualité.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titrefr' => 'required|string|max:30',
            'descriptionfr' => 'required|string|max:100',
            'titreeus' => 'nullable|string|max:30',
            'descriptioneus' => 'required|string|max:100',
            'contenueus' => 'required|string',
            'contenufr' => 'required|string',
            'type' => 'required|string',
            'dateP' => 'required|date',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $actualite = Actualite::findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Actualité non trouvée.');
        }

        $actualite->update([
            'titrefr' => $request->titrefr,
            'descriptionfr' => $request->descriptionfr,
            'titreeus' => $request->titreeus,
            'descriptioneus' => $request->descriptioneus,
            'contenueus' => $request->contenueus,
            'contenufr' => $request->contenufr,
            'type' => $request->type,
            'dateP' => $request->dateP,
            'lien' => $request->lien,
            'archive' => $request->has('archive'),
        ]);

        // Mise à jour des étiquettes
        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        } else {
            $actualite->etiquettes()->detach();
        }

        $actualite->save();

        // Ajout de nouvelles images (sans supprimer les anciennes pour cet exemple)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('actualites', 'public');
                $newDocId = Document::max('idDocument') + 1;

                $document = new Document();
                $document->idDocument = $newDocId;
                $document->nom = $file->getClientOriginalName();
                $document->chemin = $path;
                $document->type = 'image';
                $document->etat = 'actif';
                $document->save();

                $actualitteste->documents()->attach($document->idDocument);
            }
        }

        return redirect()->route('actualites.show', $id)->with('success', 'Actualité mise à jour.');
    }

    /**
     * Supprime une actualité.
     */
    public function destroy($id)
    {
        $actualite = Actualite::findOrFail($id);

        // Supprimer les relations pivots est automatique si défini en DB (ON DELETE CASCADE)
        // Sinon, on détache manuellement :
        $actualite->etiquettes()->detach();

        // Gestion des documents : faut-il supprimer les fichiers physiques ?
        // Ici on détache juste la relation
        foreach ($actualite->documents as $document) {
            Storage::disk('public')->delete($document->chemin);
            $document->delete();
        }
        $actualite->documents()->detach();

        $actualite->delete();

        return redirect()->route('admin.actualites.index')->with('success', 'Actualité supprimée.');
    }

    public function detachDocument($idActualite, $idDocument)
    {
        $actualite = Actualite::findOrFail($idActualite);
        $document = Document::findOrFail($idDocument);
        Storage::disk('public')->delete($document->chemin);
        $actualite->documents()->detach($idDocument);
        return back()->with('success', 'Image supprimée.');
    }


    /**
     * Affiche la liste des actualités pour l'admin.
     */
    public function adminIndex()
    {
        $actualites = Actualite::with(['etiquettes', 'documents'])
            ->orderBy('dateP', 'desc')
            ->paginate(20);

        return view('actualites.pannel', compact('actualites'));
    }

    /**
     * Fournit les données pour DataTables en mode serveur.
     */
    public function data() 
    {

        $query = Actualite::query();

        return DataTables::of($query)
            ->addColumn('titre', function ($actualite) {
                return $actualite->titrefr ?? 'Sans titre';
            })
            ->addColumn('etiquettes', function ($actualite) {
                return $actualite->etiquettes->pluck('nom')->join(', ');
            })
            ->addColumn('etat', function ($actualite) {
                return $actualite->archive ? 'Archivée' : 'Active';
            })
            ->addColumn('actions', function ($actualite) {
                return view('actualites.template.colonne-action', compact('actualite'));
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
