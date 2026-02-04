<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Tache;
use App\Models\DemandeHistorique;
use App\Models\Role;
use App\Http\Controllers\Traits\HandlesCsvExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemandeController extends Controller
{
    use HandlesCsvExport;

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
        
        // Charger tous les rôles (commissions) pour l'assignation
        $roles = Role::select('idRole', 'name')
            ->orderBy('name')
            ->get();
        
        return view('demandes.create', compact('types', 'urgences', 'roles'));
    }

    public function show(Tache $demande)
    {
        $demande->loadMissing(['realisateurs', 'documents', 'historiques', 'roleAssigné']);

        $metadata = [
            'reporter' => $demande->user->name ?? $demande->reporter_name ?? 'Inconnu',
            'report_date' => optional($demande->dateD)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y'),
        ];

        $photos = $demande->documents
            ? $demande->documents
                ->filter(fn($doc) => Storage::disk('public')->exists($doc->chemin))
                ->map(fn($doc) => [
                    'url' => route('demandes.document.show', ['demande' => $demande, 'document' => $doc]),
                    'nom' => $doc->nom,
                    'id' => $doc->idDocument,
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
            'type' => ['required', 'string', 'max:15'],
            'urgence' => ['required', 'string', 'max:15'],
            'dateD' => ['nullable', 'date'],
            'dateF' => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP' => ['nullable', 'numeric', 'min:0'],
            'montantR' => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer'],
            'idRole' => ['nullable', 'integer', 'exists:role,idRole'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $data = collect($validated)->except(['photos'])->toArray();
        $data['idTache'] = (Tache::max('idTache') ?? 0) + 1;
        $data['etat'] = 'En attente';
        $data['dateD'] = $validated['dateD'] ?? now();
        $data['dateF'] = $validated['dateF'] ?? null;

        $demande = Tache::create($data);

        $this->storePhotos($demande, $request->file('photos', []));
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
        
        // Charger tous les rôles (commissions) pour l'assignation
        $roles = Role::select('idRole', 'name')
            ->orderBy('name')
            ->get();

        return view('demandes.create', [
            'types' => $types,
            'urgences' => $urgences,
            'demande' => $demande,
            'roles' => $roles,
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
            'idRole' => ['nullable', 'integer', 'exists:role,idRole'],
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
        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.created'),
            $demande->description,
            0 // Dépense réelle à 0 lors de la création, elle sera complétée par les avancements
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
            'date_evenement' => now(),
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

    /**
     * Exporte toutes les demandes en CSV.
     */
    public function exportAllCsv(): StreamedResponse
    {
        $demandes = Tache::query()
            ->orderBy('dateD', 'desc')
            ->orderBy('idTache', 'desc')
            ->get();

        $filename = 'Ensemble_Des_Demandes_' . date('Y-m-d') . '.csv';
        $headers = $this->buildCsvHeaders($filename);

        $callback = function () use ($demandes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes du CSV
            fputcsv($file, [
                __('demandes.export.id'),
                __('demandes.export.date_creation'),
                __('demandes.export.titre'),
                __('demandes.export.description'),
                __('demandes.export.type'),
                __('demandes.export.urgence'),
                __('demandes.export.etat'),
                __('demandes.export.date_fin'),
                __('demandes.export.montant_previsionnel'),
                __('demandes.export.montant_reel'),
                __('demandes.export.realisateurs'),
            ], ';');

            // Données des demandes
            foreach ($demandes as $demande) {
                $demande->loadMissing(['realisateurs', 'historiques']);
                
                $realisateurs = $demande->realisateurs->pluck('name')->join(', ');
                $montantReel = $demande->historiques->sum('depense');

                fputcsv($file, [
                    $demande->idTache,
                    $this->formatDateForCsv($demande->dateD),
                    $demande->titre,
                    $demande->description,
                    $demande->type ?? '—',
                    $demande->urgence ?? '—',
                    $demande->etat ?? '—',
                    $this->formatDateForCsv($demande->dateF),
                    $this->formatMontantForCsv($demande->montantP),
                    $this->formatMontantForCsv($montantReel, true),
                    $realisateurs ?: '—',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Affiche/sert une image d'un document lié à une demande.
     */
    public function showDocument(Tache $demande, Document $document)
    {
        // Vérifier que le document appartient à la demande
        if ($document->idTache !== $demande->idTache) {
            abort(404, 'Document not found for this demande.');
        }

        // Vérifier que le fichier existe
        if (!Storage::disk('public')->exists($document->chemin)) {
            abort(404, 'File not found.');
        }

        $filePath = Storage::disk('public')->path($document->chemin);
        $mimeType = Storage::disk('public')->mimeType($document->chemin) ?? 'image/jpeg';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
        ]);
    }

}

