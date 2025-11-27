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

class ActualiteController extends Controller
{
    /**
     * Affiche la liste des actualités.
     */
    public function index()
    {

        $actualites = Actualite::with(['etiquettes', 'documents'])
            ->where('archive', false)->where('dateP', '<=', now())->whereIN('type', ['public', Auth::user() ? 'private' : ''])->orderBy('dateP', 'desc')
            ->paginate(10);
        $test = Posseder::all()->pluck('idEtiquette')->toArray();
        $etiquettes = Etiquette::all()->whereNotIn('idEtiquette', $test);
        return view('actualites.index', compact('actualites', 'etiquettes'));
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

        $actualite->save();

        // 2. Gestion des Étiquettes (Relation Pivot)
        if ($request->has('etiquettes')) {
            $actualite->etiquettes()->sync($request->etiquettes);
        }

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

        return redirect()->route('actualites.index')->with('success', 'Actualité créée avec succès.');
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
            return redirect()->route('actualites.index')->with('error', 'Actualité non trouvée.');
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
            return redirect()->route('actualites.index')->with('error', 'Actualité non trouvée.');
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

                $actualite->documents()->attach($document->idDocument);
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
}
