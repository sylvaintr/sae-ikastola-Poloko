<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DemandeController extends Controller
{
    private const DEFAULT_TYPES = ['Réparation', 'Ménage', 'Maintenance'];
    private const STATUS_TERMINE = 'Terminé';
    private const DEFAULT_ETATS = ['En attente', 'En cours', self::STATUS_TERMINE];
    private const DEFAULT_URGENCES = ['Faible', 'Moyenne', 'Élevée'];

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'etat' => $request->input('etat', 'all'),
            'type' => $request->input('type', 'all'),
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

        if ($filters['type'] && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
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
            'type' => 'type',
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

        $types = $this->loadOrDefault('type', collect(self::DEFAULT_TYPES));
        $etats = $this->loadOrDefault('etat', collect(self::DEFAULT_ETATS));
        $urgences = $this->loadOrDefault('urgence', collect(self::DEFAULT_URGENCES));

        return view('demandes.index', compact('demandes', 'filters', 'types', 'etats', 'urgences'));
    }

    public function create()
    {
        $types = $this->loadOrDefault('type', collect(self::DEFAULT_TYPES));

        $urgences = self::DEFAULT_URGENCES;
        return view('demandes.create', compact('types', 'urgences'));
    }

    public function show(Tache $demande)
    {
        $demande->loadMissing(['realisateurs']);

        $metadata = [
            'reporter' => $demande->user->name ?? $demande->reporter_name ?? 'Inconnu',
            'report_date' => optional($demande->dateD)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y'),
        ];

        $photos = []; // liaison document supprimée (pas de colonne idTache)

        // Construire un historique minimal à partir du pivot realiser
        $historiques = $demande->realisateurs->map(function (Utilisateur $user) use ($demande) {
            return (object) [
                'statut' => $demande->etat,
                'date_evenement' => $user->pivot->dateM,
                'titre' => $demande->titre,
                'responsable' => $user->name,
                'depense' => null,
                'description' => $user->pivot->description,
            ];
        });
        $totalDepense = 0;

        return view('demandes.show', compact('demande', 'metadata', 'photos', 'historiques', 'totalDepense'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:15'],
            'urgence' => ['required', 'string', 'max:15'],
            'dateD' => ['nullable', 'date'],
            'dateF' => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP' => ['nullable', 'numeric', 'min:0'],
            'montantR' => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $data = collect($validated)->except(['photos'])->toArray();
        $data['idTache'] = (Tache::max('idTache') ?? 0) + 1;
        $data['etat'] = 'En attente';
        $data['dateD'] = $validated['dateD'] ?? now();
        $data['dateF'] = $validated['dateF'] ?? null;

        $demande = Tache::create($data);

        $this->storeInitialHistory($demande);

        return to_route('demandes.index')->with('status', __('demandes.messages.created'));
    }

    public function edit(Tache $demande)
    {
        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.locked'));
        }

        $types = Tache::select('type')->distinct()->orderBy('type')->pluck('type')->filter();
        if ($types->isEmpty()) {
            $types = collect(['Réparation', 'Ménage', 'Maintenance']);
        }
        $urgences = ['Faible', 'Moyenne', 'Élevée'];

        return view('demandes.create', [
            'types' => $types,
            'urgences' => $urgences,
            'demande' => $demande,
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
            'type' => ['required', 'string', 'max:15'],
            'urgence' => ['required', 'string', 'max:15'],
            'dateD' => ['nullable', 'date'],
            'dateF' => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP' => ['nullable', 'numeric', 'min:0'],
            'montantR' => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer'],
        ]);

        $updates = collect($validated)->except(['dateD', 'dateF'])->toArray();
        $demande->update($updates);

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.updated'));
    }

    private function loadOrDefault(string $column, Collection $fallback): Collection
    {
        $values = Tache::select($column)->distinct()->orderBy($column)->pluck($column)->filter();

        return $values->isEmpty() ? $fallback : $values;
    }

    protected function storeInitialHistory(Tache $demande): void
    {
        $this->attachHistoryPivot($demande, __('demandes.history_statuses.created'), $demande->description);
    }

    public function destroy(Tache $demande)
    {
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

        $this->attachHistoryPivot(
            $demande,
            __('demandes.history_statuses.progress'),
            $validated['description'] ?? null
        );

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_added'));
    }

    public function validateDemande(Tache $demande)
    {
        $demande->update(['etat' => self::STATUS_TERMINE]);

        $this->attachHistoryPivot(
            $demande,
            __('demandes.history_statuses.done'),
            __('demandes.history_statuses.done_description')
        );

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.validated'));
    }

    /**
     * Ajoute ou met à jour une entrée pivot dans `realiser` pour tracer l'historique.
     */
    private function attachHistoryPivot(Tache $demande, string $statut, ?string $description = null): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $demande->realisateurs()->syncWithoutDetaching([
            $user->idUtilisateur => [
                'dateM' => now(),
                // On stocke le statut + description dans la colonne pivot description (pas d'autre champ dédié)
                'description' => trim($statut . ' ' . ($description ?? '')),
            ],
        ]);
    }
}

