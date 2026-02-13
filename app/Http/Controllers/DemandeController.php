<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HandlesCsvExport;
use App\Http\Controllers\Traits\HandlesCsvExport;
use App\Http\Controllers\Traits\HandlesDemandeHistory;
use App\Http\Controllers\Traits\HandlesDemandePhotos;
use App\Models\DemandeHistorique;
use App\Models\DemandeHistorique;
use App\Models\Document;
use App\Models\Document;
use App\Models\Evenement;
use App\Models\Role;
use App\Models\Role;
use App\Models\Tache;
use App\Models\Tache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemandeController extends Controller
{
    use HandlesCsvExport, HandlesDemandeHistory, HandlesDemandePhotos;

    private const DEFAULT_TYPES    = ['Réparation', 'Ménage', 'Maintenance'];
    private const STATUS_TERMINE   = 'Terminé';
    private const DEFAULT_ETATS    = ['En attente', 'En cours', self::STATUS_TERMINE];
    private const DEFAULT_URGENCES = ['Faible', 'Moyenne', 'Élevée'];
    private const PER_PAGE         = 10;

    /**
     * Méthode utilitaire pour extraire les filtres de la requête avec des valeurs par défaut.
     * @param Request $request fournissant les paramètres de filtrage et de tri
     * @return array<string,mixed> tableau associatif des filtres extraits (search, etat, type, urgence, date_from, date_to, sort, direction)
     */
    private function canManageDemande(Tache $demande): bool
    {
        return [
            'search'    => $request->input('search'),
            'etat'      => $request->input('etat', 'all'),
            'type'      => $request->input('type', 'all'),
            'urgence'   => $request->input('urgence', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to'   => $request->input('date_to'),
            'sort'      => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];
    }

    /**
     * Méthode utilitaire pour construire la requête de récupération des demandes en fonction des filtres fournis.
     * @param array<string,mixed> $filters tableau associatif des filtres (search, etat, type, urgence, date_from, date_to)
     * @return Builder instance de la requête Eloquent construite avec les conditions de filtrage appliquées
     */
    private function authorizeManageDemande(Tache $demande)
    {
        $query = Tache::query();

        if (! empty($filters['search'])) {
            $searchTerm = trim((string) $filters['search']);

            $query->where(function ($q) use ($searchTerm) {
                // Si l'utilisateur tape un ID (numérique), on fait une recherche exacte sur l'ID
                // pour éviter que "1" matche 1,10,11,21,... ou via le titre.
                if (ctype_digit($searchTerm)) {
                    $q->where('idTache', (int) $searchTerm);
                    return;
                }

                // Sinon, recherche texte (ID partiel ou titre)
                $q->where('idTache', 'like', '%' . $searchTerm . '%')
                    ->orWhere('titre', 'like', '%' . $searchTerm . '%');
            });
        }

        if (! empty($filters['etat']) && $filters['etat'] !== 'all') {
            $query->where('etat', $filters['etat']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['urgence']) && $filters['urgence'] !== 'all') {
            $query->where('urgence', $filters['urgence']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('dateD', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('dateD', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Méthode utilitaire pour résoudre le champ de tri et la direction à partir des filtres fournis.
     * @param array<string,mixed> $filters tableau associatif des filtres (sort, direction)
     * @return array{0:string,1:string} tableau contenant le champ de tri résolu et la direction ('asc' ou 'desc')
     */
    private function resolveSort(array $filters): array
    {
        $sortable = [
            'id'      => 'idTache',
            'date'    => 'dateD',
            'title'   => 'titre',
            'type'    => 'type',
            'urgence' => 'urgence',
            'etat'    => 'etat',
        ];

        $sortKey   = (string) ($filters['sort'] ?? 'date');
        $sortField = $sortable[$sortKey] ?? $sortable['date'];
        $direction = strtolower((string) ($filters['direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        return [$sortField, $direction];
    }

    /**
     * Méthode d'affichage de la liste des demandes avec support de la recherche, du filtrage, du tri et de la pagination.
     * @param Request $request fournissant les paramètres de filtrage, de tri et de pagination
     * @return View la vue rendue avec les données des demandes, les filtres appliqués et les options de type/état/urgence
     */
    public function index(Request $request): View
    {
        $filters                 = $this->extractFilters($request);
        $query                   = $this->buildDemandesQuery($filters);
        [$sortField, $direction] = $this->resolveSort($filters);

        $demandes = $query
            ->orderBy($this->sortableField($filters['sort']), $this->sortDirection($filters['direction']))
            ->orderBy('idTache', 'desc')
            ->paginate(10)
            ->withQueryString();

        $types      = $this->loadOrDefault('type', collect(self::DEFAULT_TYPES));
        $etats      = $this->loadOrDefault('etat', collect(self::DEFAULT_ETATS));
        $urgences   = $this->loadOrDefault('urgence', collect(self::DEFAULT_URGENCES));
        $evenements = Evenement::orderBy('titre')->get();

        // Préparer les infos d'autorisation pour la vue
        $user        = Auth::user();
        $isCA        = $user?->can('gerer-demande') ?? false;
        $userRoleIds = $user ? $user->rolesCustom()->pluck('role.idRole')->toArray() : [];

        return view('demandes.index', compact('demandes', 'filters', 'etats', 'urgences', 'evenements', 'isCA', 'userRoleIds'));
    }

    /**
     * Méthode d'affichage du formulaire de création d'une nouvelle demande, avec chargement des types, urgences et rôles disponibles pour l'assignation.
     * @return View la vue du formulaire de création de demande avec les données nécessaires (types, urgences, rôles)
     */
    public function create()
    {
        $types    = $this->loadOrDefault('type', collect(self::DEFAULT_TYPES));
        $urgences = self::DEFAULT_URGENCES;

        // Charger tous les rôles (commissions) pour l'assignation
        $roles = Role::select('idRole', 'name')
            ->orderBy('name')
            ->get();

        return view('demandes.create', compact('types', 'urgences', 'roles'));
    }

    /**
     * Méthode d'affichage des détails d'une demande spécifique, avec chargement des relations (réalisateurs, documents, historiques) et préparation des métadonnées pour la vue.
     * @param Tache $demande l'instance de la demande à afficher (résolue via route model binding)
     * @return View la vue des détails de la demande avec les données de la demande, les métadonnées préparées, les photos liées et l'historique des avancements
     */
    public function show(Tache $demande)
    {
        $demande->loadMissing(['realisateurs', 'documents', 'historiques', 'roles']);

        // Récupérer le créateur depuis l'historique initial
        $initialHistory = $demande->historiques->where('statut', __('demandes.history_statuses.created'))->first();
        $reporter       = $initialHistory?->responsable ?? 'Inconnu';

        $metadata = [
            'reporter'    => $demande->user->name ?? $demande->reporter_name ?? 'Inconnu',
            'report_date' => optional($demande->dateD)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y'),
        ];

        $photos = $demande->documents
            ? $demande->documents
            ->filter(fn($doc) => Storage::disk('public')->exists($doc->chemin))
            ->map(fn($doc) => [
                'url' => route('demandes.document.show', ['demande' => $demande, 'document' => $doc]),
                'nom' => $doc->nom,
                'id'  => $doc->idDocument,
            ])->values()->all()
            : [];

        $historiques  = $demande->historiques;
        $totalDepense = $historiques->sum('depense');
        $canManage    = $this->canManageDemande($demande);

        return view('demandes.show', compact('demande', 'metadata', 'photos', 'historiques', 'totalDepense', 'canManage'));
    }

    /**
     * Méthode de traitement du formulaire de création d'une nouvelle demande, avec validation des données, création de la demande, sauvegarde des photos liées et redirection vers la liste des demandes avec un message de succès.
     * @param Request $request fournissant les données du formulaire de création de demande
     * @return RedirectResponse redirection vers la liste des demandes avec un message de succès
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre'       => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:100'],
            'type'        => ['required', 'string', 'max:15'],
            'urgence'     => ['required', 'string', 'max:15'],
            'dateD'       => ['nullable', 'date'],
            'dateF'       => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP'    => ['nullable', 'numeric', 'min:0'],
            'montantR'    => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer'],
            'idRole'      => ['required', 'integer', 'exists:role,idRole'],
            'photos'      => ['nullable', 'array', 'max:4'],
            'photos.*'    => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $data            = collect($validated)->except(['photos'])->toArray();
        $data['idTache'] = (Tache::max('idTache') ?? 0) + 1;
        $data['etat']    = 'En attente';
        $data['dateD']   = $validated['dateD'] ?? now();
        $data['dateF']   = $validated['dateF'] ?? null;

        $demande = Tache::create($data);

        // Synchroniser les rôles
        if (! empty($validated['roles'])) {
            $demande->roles()->sync($validated['roles']);
        }

        $this->storePhotos($demande, $request->file('photos', []));
        $this->storeInitialHistory($demande);

        return to_route('demandes.index')->with('status', __('demandes.messages.created'));
    }

    /**
     * Méthode d'affichage du formulaire d'édition d'une demande existante, avec chargement des types, urgences et rôles disponibles pour l'assignation, et vérification de l'état de la demande pour empêcher l'édition si elle est terminée.
     * @param Tache $demande l'instance de la demande à éditer (résolue via route model binding)
     * @return View|RedirectResponse la vue du formulaire d'édition de demande avec les données nécessaires (types, urgences, rôles) ou redirection vers les détails de la demande avec un message si elle est verrouillée
     */
    public function edit(Tache $demande): View | RedirectResponse
    {
        $this->authorizeManageDemande($demande);

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
            'types'    => $types,
            'urgences' => $urgences,
            'demande'  => $demande,
            'roles'    => $roles,
        ]);
    }

    /**
     * Méthode de traitement du formulaire d'édition d'une demande existante, avec validation des données, mise à jour de la demande, et redirection vers les détails de la demande avec un message de succès ou de verrouillage si la demande est terminée.
     * @param Request $request fournissant les données du formulaire d'édition de demande
     * @param Tache $demande l'instance de la demande à mettre à jour (résolue via route model binding)
     * @return RedirectResponse redirection vers les détails de la demande avec un message de succès ou de verrouillage
     */
    public function update(Request $request, Tache $demande): RedirectResponse
    {
        $this->authorizeManageDemande($demande);

        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.locked'));
        }

        $validated = $request->validate([
            'titre'       => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:100'],
            'type'        => ['required', 'string', 'max:15'],
            'urgence'     => ['required', 'string', 'max:15'],
            'dateD'       => ['nullable', 'date'],
            'dateF'       => ['nullable', 'date', 'after_or_equal:dateD'],
            'montantP'    => ['nullable', 'numeric', 'min:0'],
            'montantR'    => ['nullable', 'numeric', 'min:0'],
            'idEvenement' => ['nullable', 'integer'],
            'idRole'      => ['required', 'integer', 'exists:role,idRole'],
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
        $filters = $this->demandeFiltersFromRequest($request);
        $query   = $this->buildDemandeQuery($filters);

        $demandes = $query
            ->orderBy($this->sortableField($filters['sort']), $this->sortDirection($filters['direction']))
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

        foreach ($demandes as $demande) {
            $csv .= implode(';', $this->formatDemandeToCsvRow($demande)) . "\n";
        }

        return response($csv)
            ->header('Content-Type', self::CSV_CONTENT_TYPE)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Extrait les filtres de la requête pour les demandes.
     */
    private function demandeFiltersFromRequest(Request $request): array
    {
        return [
            'search'    => $request->input('search'),
            'etat'      => $request->input('etat', 'all'),
            'urgence'   => $request->input('urgence', 'all'),
            'evenement' => $request->input('evenement', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to'   => $request->input('date_to'),
            'sort'      => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];
    }

    /**
     * Construit une requête filtrée pour les demandes.
     */
    private function buildDemandeQuery(array $filters): Builder
    {
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

        return $query;
    }

    /**
     * Retourne un tableau prêt pour enregistrer en CSV à partir d'une demande.
     */
    private function formatDemandeToCsvRow(Tache $demande): array
    {
        $roles = $demande->roles->pluck('name')->implode(', ');

        return [
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
    }

    /**
     * Retourne le champ sortable correspondant.
     */
    private function sortableField(string $sort): string
    {
        $map = [
            'id'      => 'idTache',
            'date'    => 'dateD',
            'title'   => 'titre',
            'urgence' => 'urgence',
            'etat'    => 'etat',
        ];

        return $map[$sort] ?? $map['date'];
    }

    /**
     * Normalise la direction de tri.
     */
    private function sortDirection(string $dir): string
    {
        return strtolower($dir) === 'asc' ? 'asc' : 'desc';
    }

    /**
     * Exporte toutes les demandes avec historique en CSV.
     */
    public function exportAllCsv(Request $request): StreamedResponse
    {
        $filters = $this->demandeFiltersFromRequest($request);
        $query   = $this->buildDemandeQuery($filters)
            ->with(['historiques', 'realisateurs', 'roles']);

        $demandes = $query
            ->orderBy($this->sortableField($filters['sort']), $this->sortDirection($filters['direction']))
            ->orderBy('idTache', 'desc')
            ->get();

        $filename = 'demandes_export_' . date('Y-m-d') . '.csv';
        $headers  = $this->buildCsvHeaders($filename);

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

    public function destroy(Tache $demande)
     * Méthode utilitairepourcréeruneentréed'historique initiale lors de la création d'une nouvelledemande, avec lestatut"Créé"et unedépenseréelleà0.
     * @param Tache $demandel'instance de la demande pour laquelle créer l'entrée d'historique initiale
     */
    protected function storeInitialHistory(Tache $demande): void
    {
        $this->addHistoryEntry(
            $demande,
            __('demandes . history_statuses . created'),
            $demande->description,
            0// Dépense réelle à 0 lors de la création, elle sera complétée par les avancements
        );
    }

    /**
     * Méthode de suppression d'une demande, avec suppressiondesdocumentsliés(fichiers etenregistrements), de l'historique associé, des relations avec les réalisateurs, et de la demande elle-même, suivie d'une redirectionverslalistedesdemandesavecunmessagedesuccès .
     * @param Tache $demandel'instance de la demande à supprimer (résolue via route model binding)
     * @return RedirectResponse redirection vers la liste des demandes avec un message de succès
     * @throws \Exception en cas d'erreur lorsdelasuppressiondesfichiersoudesenregistrementsliés
     *  /
    public function destroy(Tache $demande): RedirectResponse {
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

    /**
     * Méthode d'affichage du formulaire d'ajout d'une nouvelle entrée d'historique pour une demande spécifique, avec vérification de l'état de la demande pour empêcher l'ajout d'historique si elle est terminée, et redirection vers les détails de la demande avec un message si elle est verrouillée.
     * @param Tache $demande l'instance de la demande pour laquelle ajouter une entrée d'historique (résolue via route model binding)
     * @return View|RedirectResponse la vue du formulaire d'ajout d'historique ou redirection vers les détails de la demande avec un message si elle est verrouillée
     */
    public function createHistorique(Tache $demande): View | RedirectResponse
    {
        $this->authorizeManageDemande($demande);

        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_locked'));
        }

        return view('demandes.historique.create', compact('demande'));
    }

    /**
     * Méthode de traitement du formulaire d'ajout d'une nouvelle entrée d'historique pour une demande spécifique, avec validation des données, création de l'entrée d'historique, et redirection vers les détails de la demande avec un message de succès ou de verrouillage si la demande est terminée.
     * @param Request $request fournissant les données du formulaire d'ajout d'historique
     * @param Tache $demande l'instance de la demande pour laquelle ajouter une entrée d'historique (résolue via route model binding)
     * @return RedirectResponse redirection vers les détails de la demande avec un message de succès ou de verrouillage
     * @throws \Illuminate\Validation\ValidationException en cas de données invalides fournies dans le formulaire d'ajout d'historique
     */
    public function storeHistorique(Request $request, Tache $demande): RedirectResponse
    {
        $this->authorizeManageDemande($demande);

        if ($demande->etat === self::STATUS_TERMINE) {
            return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_locked'));
        }

        $validated = $request->validate([
            'titre'       => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'depense'     => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->addHistoryEntry(
            $demande,
            __('demandes.history_statuses.progress'),
            $validated['description'] ?? null,
            $validated['depense'] ?? null
        );

        return to_route('demandes.show', $demande)->with('status', __('demandes.messages.history_added'));
    }

    /**
     * Méthode de validation d'une demande, qui met à jour son état à "Terminé", ajoute une entrée d'historique correspondante avec le statut "Terminé" et la dépense réelle finale, puis redirige vers les détails de la demande avec un message de succès.
     * @param Tache $demande l'instance de la demande à valider (résolue via route model binding)
     * @return RedirectResponse redirection vers les détails de la demande avec un message de succès
     */
    public function validateDemande(Tache $demande): RedirectResponse
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

}
