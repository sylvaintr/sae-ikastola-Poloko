<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Tache;
use App\Models\Role;
use App\Models\Evenement;
use App\Models\DemandeHistorique;
use App\Http\Controllers\Traits\HandlesCsvExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemandeController extends Controller
{
    use HandlesCsvExport;
    private const STATUS_TERMINE = 'Terminé';
    private const DEFAULT_ETATS = ['En attente', 'En cours', self::STATUS_TERMINE];
    private const DEFAULT_URGENCES = ['Faible', 'Moyenne', 'Élevée'];

    /**
     * Vérifie si l'utilisateur connecté peut modifier/valider/supprimer la demande.
     * Seuls les membres du CA et les utilisateurs ayant un rôle concerné par la demande sont autorisés.
     */
    private function canManageDemande(Tache $demande): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Les utilisateurs avec la permission gerer-demande ont toujours accès
        if ($user->can('gerer-demande')) {
            return true;
        }

        // Vérifier si l'utilisateur a un des rôles associés à la demande
        $demandeRoleIds = $demande->roles()->pluck('role.idRole')->toArray();
        $userRoleIds = $user->rolesCustom()->pluck('role.idRole')->toArray();

        // Autorisé si la demande a des rôles ET l'utilisateur en possède au moins un
        return !empty($demandeRoleIds) && !empty(array_intersect($demandeRoleIds, $userRoleIds));
    }

    /**
     * Retourne une réponse 403 si l'utilisateur n'est pas autorisé.
     */
    private function authorizeManageDemande(Tache $demande)
    {
        if (!$this->canManageDemande($demande)) {
            abort(403, __('demandes.messages.unauthorized'));
        }
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'etat' => $request->input('etat', 'all'),
            'urgence' => $request->input('urgence', 'all'),
            'evenement' => $request->input('evenement', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort' => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = Tache::query();
        $query->where('type', 'demande');

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

        if ($filters['evenement'] && $filters['evenement'] !== 'all') {
            if ($filters['evenement'] === 'none') {
                $query->whereNull('idEvenement');
            } else {
                $query->where('idEvenement', $filters['evenement']);
            }
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
            ->with('roles')
            ->orderBy($sortField, $direction)
            ->orderBy('idTache', 'desc')
            ->paginate(10)
            ->withQueryString();

        $etats = $this->loadOrDefault('etat', collect(self::DEFAULT_ETATS));
        $urgences = $this->loadOrDefault('urgence', collect(self::DEFAULT_URGENCES));
        $evenements = Evenement::orderBy('titre')->get();

        // Préparer les infos d'autorisation pour la vue
        $user = Auth::user();
        $isCA = $user?->can('gerer-demande') ?? false;
        $userRoleIds = $user ? $user->rolesCustom()->pluck('role.idRole')->toArray() : [];

        return view('demandes.index', compact('demandes', 'filters', 'etats', 'urgences', 'evenements', 'isCA', 'userRoleIds'));
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
        $demande->loadMissing(['realisateurs', 'documents', 'historiques', 'roles']);

        // Récupérer le créateur depuis l'historique initial
        $initialHistory = $demande->historiques->where('statut', __('demandes.history_statuses.created'))->first();
        $reporter = $initialHistory?->responsable ?? 'Inconnu';

        $metadata = [
            'reporter' => $reporter,
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
        $canManage = $this->canManageDemande($demande);

        return view('demandes.show', compact('demande', 'metadata', 'photos', 'historiques', 'totalDepense', 'canManage'));
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
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        $data = collect($validated)->except(['photos', 'roles'])->toArray();
        $data['idTache'] = (Tache::max('idTache') ?? 0) + 1;
        $data['etat'] = 'En attente';
        $data['dateD'] = $validated['dateD'] ?? now();
        $data['dateF'] = $validated['dateF'] ?? null;
        $data['type'] = 'demande';

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
        $this->authorizeManageDemande($demande);

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
        $this->authorizeManageDemande($demande);

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
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:role,idRole'],
        ]);

        $updates = collect($validated)->except(['dateD', 'dateF', 'roles'])->toArray();
        $demande->update($updates);

        // Synchroniser les rôles (vide si aucun rôle sélectionné)
        $demande->roles()->sync($validated['roles'] ?? []);

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.updated'));
    }

    /**
     * Exporte les demandes en CSV.
     */
    public function export(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'etat' => $request->input('etat', 'all'),
            'urgence' => $request->input('urgence', 'all'),
            'evenement' => $request->input('evenement', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort' => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = Tache::with('roles')->where('type', 'demande');

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

        if ($filters['evenement'] && $filters['evenement'] !== 'all') {
            if ($filters['evenement'] === 'none') {
                $query->whereNull('idEvenement');
            } else {
                $query->where('idEvenement', $filters['evenement']);
            }
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
            ->get();

        $filename = 'demandes_' . now()->format('Y-m-d_His') . '.csv';

        // BOM UTF-8 pour Excel
        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF);

        // En-têtes CSV
        $csv .= implode(';', [
            'ID',
            'Titre',
            'Description',
            'Urgence',
            'État',
            'Date début',
            'Date fin',
            'Montant prévu (€)',
            'Montant réel (€)',
            'Rôles cibles',
        ]) . "\n";

        // Données
        foreach ($demandes as $demande) {
            $roles = $demande->roles->pluck('name')->implode(', ');

            $row = [
                $demande->idTache,
                '"' . str_replace('"', '""', $demande->titre ?? '') . '"',
                '"' . str_replace('"', '""', $demande->description ?? '') . '"',
                $demande->urgence ?? '',
                $demande->etat ?? '',
                optional($demande->dateD)->format('d/m/Y') ?? '',
                optional($demande->dateF)->format('d/m/Y') ?? '',
                $demande->montantP ? number_format($demande->montantP, 2, ',', ' ') : '',
                $demande->montantR ? number_format($demande->montantR, 2, ',', ' ') : '',
                '"' . str_replace('"', '""', $roles) . '"',
            ];

            $csv .= implode(';', $row) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Exporte toutes les demandes avec historique en CSV.
     */
    public function exportAllCsv(Request $request): StreamedResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'etat' => $request->input('etat', 'all'),
            'urgence' => $request->input('urgence', 'all'),
            'evenement' => $request->input('evenement', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort' => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = Tache::with(['historiques', 'realisateurs', 'roles'])->where('type', 'demande');

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

        if ($filters['evenement'] && $filters['evenement'] !== 'all') {
            if ($filters['evenement'] === 'none') {
                $query->whereNull('idEvenement');
            } else {
                $query->where('idEvenement', $filters['evenement']);
            }
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
            ->get();

        $filename = 'demandes_export_' . date('Y-m-d') . '.csv';
        $headers = $this->buildCsvHeaders($filename);

        $callback = function () use ($demandes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            foreach ($demandes as $demande) {
                $this->writeDemandeSection($file, $demande);
                $this->writeHistoriqueSection($file, $demande->historiques);
                fputcsv($file, [], ';');
                fputcsv($file, ['---'], ';');
                fputcsv($file, [], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function loadOrDefault(string $column, Collection $fallback): Collection
    {
        $values = Tache::select($column)->where('type', 'demande')->distinct()->orderBy($column)->pluck($column)->filter();

        return $values->isEmpty() ? $fallback : $values;
    }

    protected function storeInitialHistory(Tache $demande): void
    {
        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.created'),
            $demande->description
        );
    }

    public function destroy(Tache $demande)
    {
        $this->authorizeManageDemande($demande);

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
        $this->authorizeManageDemande($demande);

        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_locked'));
        }

        return view('demandes.historique.create', compact('demande'));
    }

    public function storeHistorique(Request $request, Tache $demande)
    {
        $this->authorizeManageDemande($demande);

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
        $this->authorizeManageDemande($demande);

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

