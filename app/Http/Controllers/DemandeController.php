<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Tache;
use App\Models\Role;
use App\Models\Evenement;
use App\Models\DemandeHistorique;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DemandeController extends Controller
{
    private const STATUS_TERMINE = 'Terminé';
    private const DEFAULT_ETATS = ['En attente', 'En cours', self::STATUS_TERMINE];
    private const DEFAULT_URGENCES = ['Faible', 'Moyenne', 'Élevée'];

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'etat' => $request->input('etat', 'all'),
            'urgence' => $request->input('urgence', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort' => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = Tache::query();

        if ($filters['search']) {
            $searchTerm = trim($filters['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('idTache', 'like', '%' . $searchTerm . '%')
                    ->orWhere('titre', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($filters['etat'] && $filters['etat'] !== 'all') {
            $query->where('etat', $filters['etat']);
        }

        if ($filters['urgence'] && $filters['urgence'] !== 'all') {
            $query->where('urgence', $filters['urgence']);
        }

        if ($filters['date_from']) {
            $query->whereDate('dateD', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('dateD', '<=', $filters['date_to']);
        }

        $sortable = [
            'id' => 'idTache',
            'date' => 'dateD',
            'title' => 'titre',
            'urgence' => 'urgence',
            'etat' => 'etat',
        ];

        $sortField = $sortable[$filters['sort']] ?? $sortable['date'];
        $direction = strtolower($filters['direction']) === 'asc' ? 'asc' : 'desc';

        $demandes = $query
            ->orderBy($sortField, $direction)
            ->orderBy('idTache', 'desc')
            ->paginate(10)
            ->withQueryString();

        $etats = $this->loadOrDefault('etat', collect(self::DEFAULT_ETATS));
        $urgences = $this->loadOrDefault('urgence', collect(self::DEFAULT_URGENCES));

        return view('demandes.index', compact('demandes', 'filters', 'etats', 'urgences'));
    }

    public function create()
    {
        $urgences = self::DEFAULT_URGENCES;
        $roles = Role::orderBy('name')->get();
        $evenements = Evenement::with('roles')->orderBy('titre')->get();

        return view('demandes.create', compact('urgences', 'roles', 'evenements'));
    }

    public function show(Tache $demande)
    {
        $demande->loadMissing(['realisateurs', 'documents', 'historiques']);

        $metadata = [
            'reporter' => $demande->user->name ?? $demande->reporter_name ?? 'Inconnu',
            'report_date' => optional($demande->dateD)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y'),
        ];

        $photos = $demande->documents
            ? $demande->documents
                ->filter(fn($doc) => Storage::disk('public')->exists($doc->chemin))
                ->map(fn($doc) => [
                    'url' => Storage::url($doc->chemin),
                    'nom' => $doc->nom,
                ])->values()->all()
            : [];

        $historiques = $demande->historiques;
        $totalDepense = $historiques->sum('depense');

        return view('demandes.show', compact('demande', 'metadata', 'photos', 'historiques', 'totalDepense'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:100'],
            'urgence' => ['required', 'string', 'max:15'],
            'dateD' => ['nullable', 'date'],
            'dateF' => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP' => ['nullable', 'numeric', 'min:0'],
            'montantR' => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer', 'exists:evenement,idEvenement'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        $data = collect($validated)->except(['photos', 'roles'])->toArray();
        $data['idTache'] = (Tache::max('idTache') ?? 0) + 1;
        $data['etat'] = 'En attente';
        $data['dateD'] = $validated['dateD'] ?? now();
        $data['dateF'] = $validated['dateF'] ?? null;

        $demande = Tache::create($data);

        // Synchroniser les rôles
        if (!empty($validated['roles'])) {
            $demande->roles()->sync($validated['roles']);
        }

        $this->storePhotos($demande, $request->file('photos', []));
        $this->storeInitialHistory($demande);

        return to_route('demandes.index')->with('status', __('demandes.messages.created'));
    }

    public function edit(Tache $demande)
    {
        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.locked'));
        }

        $urgences = ['Faible', 'Moyenne', 'Élevée'];
        $roles = Role::orderBy('name')->get();
        $evenements = Evenement::with('roles')->orderBy('titre')->get();

        // Charger les rôles de la demande
        $demande->load('roles');

        return view('demandes.create', [
            'urgences' => $urgences,
            'demande' => $demande,
            'roles' => $roles,
            'evenements' => $evenements,
        ]);
    }

    public function update(Request $request, Tache $demande)
    {
        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.locked'));
        }

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:100'],
            'urgence' => ['required', 'string', 'max:15'],
            'dateD' => ['nullable', 'date'],
            'dateF' => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP' => ['nullable', 'numeric', 'min:0'],
            'montantR' => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer', 'exists:evenement,idEvenement'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        $updates = collect($validated)->except(['dateD', 'dateF', 'roles'])->toArray();
        $demande->update($updates);

        // Synchroniser les rôles (vide si aucun rôle sélectionné)
        $demande->roles()->sync($validated['roles'] ?? []);

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.updated'));
    }

    private function loadOrDefault(string $column, Collection $fallback): Collection
    {
        $values = Tache::select($column)->distinct()->orderBy($column)->pluck($column)->filter();

        return $values->isEmpty() ? $fallback : $values;
    }

    protected function storeInitialHistory(Tache $demande): void
    {
        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.created'),
            $demande->description,
            $demande->montantP
        );
    }

    public function destroy(Tache $demande)
    {
        // Supprimer les documents liés (fichiers + enregistrements)
        foreach ($demande->documents as $doc) {
            // Supprimer le fichier physique
            Storage::disk('public')->delete($doc->chemin);
            // Supprimer les relations dans la table pivot 'contenir' (utilisateurs-documents)
            $doc->utilisateurs()->detach();
            // Supprimer les relations dans la table pivot 'joindre' (actualités-documents)
            $doc->actualites()->detach();
            // Supprimer l'enregistrement du document
            $doc->delete();
        }

        // Supprimer l'historique lié
        DemandeHistorique::where('idDemande', $demande->idTache)->delete();

        // Supprimer les relations dans la table pivot 'realiser' (utilisateurs-tâches)
        $demande->realisateurs()->detach();

        // Détacher les rôles de la demande
        $demande->roles()->detach();

        // Supprimer la demande elle-même
        $demande->delete();

        return to_route('demandes.index')->with('status', __('demandes.messages.deleted'));
    }

    public function createHistorique(Tache $demande)
    {
        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_locked'));
        }

        return view('demandes.historique.create', compact('demande'));
    }

    public function storeHistorique(Request $request, Tache $demande)
    {
        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_locked'));
        }

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'depense' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.progress'),
            $validated['description'] ?? null,
            $validated['depense'] ?? null
        );

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_added'));
    }

    public function validateDemande(Tache $demande)
    {
        $demande->update(['etat' => self::STATUS_TERMINE]);

        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.done'),
            __('demandes.history_statuses.done_description'),
            $demande->montantR ?? null
        );

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.validated'));
    }

    /**
     * Ajoute une entrée dans demande_historique.
     */
    private function addHistoryEntry(Tache $demande, string $statut, ?string $description = null, ?float $depense = null): void
    {
        $user = Auth::user();

        DemandeHistorique::create([
            'idDemande' => $demande->idTache,
            'statut' => $statut,
            'titre' => $demande->titre,
            'responsable' => $user?->name ?? '',
            'depense' => $depense,
            'dateE' => now(),
            'description' => $description,
        ]);
    }

    /**
     * Sauvegarde les photos liées à la demande (si présentes).
     */
    protected function storePhotos(Tache $demande, array $files): void
    {
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $path = $file->store('demandes', 'public');

            Document::create([
                'idTache' => $demande->idTache,
                'nom' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'chemin' => $path,
                'type' => substr($file->extension(), 0, 5),
                'etat' => 'actif',
            ]);
        }
    }
}

