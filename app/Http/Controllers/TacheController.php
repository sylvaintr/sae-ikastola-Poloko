<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\TacheHistorique;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TacheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'etat' => $request->input('etat', 'all'),
            'urgence' => $request->input('urgence', 'all'),
            'date_min' => $request->input('date_min'),
            'date_max' => $request->input('date_max'),
            'sort' => $request->input('sort', 'date'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = Tache::query()->with('realisateurs');

        if ($filters['search']) {
            $search = Str::of($filters['search'])->lower()->ascii();
            $query->where(function ($q) use ($search) {
                $q->where('idTache', 'like', "%{$search}%")
                    ->orWhere('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('realisateurs', function ($qr) use ($search) {
                        $qr->where('prenom', 'like', "%{$search}%")
                            ->orWhere('nom', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['etat'] && $filters['etat'] !== 'all') {
            $query->where('etat', $filters['etat']);
        }

        if ($filters['urgence'] && $filters['urgence'] !== 'all') {
            $query->where('type', $filters['urgence']);
        }

        if ($filters['date_min']) {
            $query->whereDate('dateD', '>=', $filters['date_min']);
        }

        if ($filters['date_max']) {
            $query->whereDate('dateD', '<=', $filters['date_max']);
        }

        $sortable = [
            'id' => 'idTache',
            'date' => 'dateD',
            'title' => 'titre',
            'assignation' => 'assignation',
            'urgence' => 'type',
            'etat' => 'etat',
        ];

        $sortKey = $filters['sort'] ?? 'date';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortField = $sortable[$sortKey] ?? $sortable['date'];

        if ($sortField === 'assignation') {
            $query->orderByRaw(
                "(SELECT CONCAT(u.prenom, ' ', u.nom) FROM realiser r " .
                "JOIN utilisateur u ON u.idUtilisateur = r.idUtilisateur " .
                "WHERE r.idTache = tache.idTache " .
                "ORDER BY u.prenom, u.nom LIMIT 1) {$direction}"
            );
        } else {
            $query->orderBy($sortField, $direction);
        }

        $taches = $query
            ->orderBy('idTache', 'desc')
            ->paginate(10)
            ->withQueryString();

        $etats = [
            'todo' => 'En attente',
            'doing' => 'En cours',
            'done' => 'Terminé',
        ];

        $urgences = [
            'low' => 'Faible',
            'medium' => 'Moyenne',
            'high' => 'Élevée',
        ];

        return view('tache.index', compact('taches', 'filters', 'etats', 'urgences'));
    }

    public function getDatatable(Request $request)
{
    if (!$request->ajax()) {
        return view('tache.index');
    }

    $query = Tache::query();
    $this->applyFilters($query, $request);

    return DataTables::of($query)
        ->editColumn('dateD', fn ($row) => $this->formatDate($row))
        ->editColumn('etat', fn ($row) => $this->formatEtat($row->etat))
        ->addColumn('assignation', fn ($tache) => $this->formatAssignation($tache))
        ->addColumn('urgence', fn ($row) => $this->formatUrgence($row->type))
        ->addColumn('action', fn ($row) => $this->buildActions($row))
        ->rawColumns(['action'])
        ->make(true);
}

private function applyFilters($query, Request $request)
{
    if ($request->filled('search_global')) {
        $search = Str::of($request->search_global)->lower()->ascii();

        $query->where(function ($q) use ($search) {
            $q->where('idTache', 'like', "%{$search}%")
              ->orWhere('titre', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('realisateurs', function ($qr) use ($search) {
                  $qr->where('prenom', 'like', "%{$search}%")
                     ->orWhere('nom', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    if ($request->filled('etat')) {
        $query->where('etat', $request->etat);
    }

    if ($request->filled('urgence')) {
        $query->where('type', $request->urgence);
    }

    if ($request->filled('date_min')) {
        $query->whereDate('dateD', '>=', $request->date_min);
    }

    if ($request->filled('date_max')) {
        $query->whereDate('dateD', '<=', $request->date_max);
    }
}

private function formatDate($row)
{
    return \Carbon\Carbon::parse($row->dateD)->format('d/m/Y');
}

private function formatEtat($etat)
{
    return match ($etat) {
        'done' => 'Terminé',
        'doing' => 'En cours',
        default => 'En attente',
    };
}

private function formatUrgence($type)
{
    return match ($type) {
        'low' => 'Faible',
        'medium' => 'Moyenne',
        default => 'Élevée',
    };
}

private function formatAssignation($tache)
{
    $first = $tache->realisateurs->first();

    if (!$first) {
        return '—';
    }

    return $first->prenom . ' ' . strtoupper(substr($first->nom, 0, 1)) . '.';
}


    public function create()
    {
        $utilisateurs = Utilisateur::orderBy('prenom')->limit(150)->get();
        return view('tache.form', compact('utilisateurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:low,medium,high',
            'dateD' => 'required|date',
            'realisateurs' => 'required|array|min:1',
            'realisateurs.*' => 'required|integer|exists:utilisateur,idUtilisateur'
        ]);

        $tache = Tache::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'etat' => 'todo',
            'dateD' => $validated['dateD'],
        ],
       
        );
       

        // attacher realisateurs (pivot)
        if (!empty($validated['realisateurs'])) {
            foreach ($validated['realisateurs'] as $uId) {
                $tache->realisateurs()->attach($uId, ['dateM' => now(), 'description' => null]);
            }
        }
        // historique initial avec création de la tâche
        TacheHistorique::create([
            'idTache' => $tache->idTache,
            'statut' => __('taches.history_statuses.created'),
            'titre' => $tache->titre,
            'urgence' => $tache->type,
            'description' => $tache->description,
            'modifie_par' => auth()->user()->idUtilisateur ?? null,
        ]);

        return to_route('tache.index')->with('status', __('taches.messages.created'));
    }

    public function edit(Tache $tache)
    {
        if ($tache->etat === "done") {
            return to_route('tache.show', $tache)->with('status', __('taches.messages.locked'));
        }

        // eager load realisateurs pour préremplir
        $tache->load('realisateurs');

        $utilisateurs = Utilisateur::orderBy('prenom')->limit(150)->get();

        return view('tache.form', compact('tache', 'utilisateurs'));
    }

    public function update(Request $request, Tache $tache)
    {
        if ($tache->etat === "done") {
            return to_route('tache.show', $tache)->with('status', __('taches.messages.locked'));
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:low,medium,high',
            'dateD' => 'required|date',
            
            'realisateurs' => 'required|array|min:1',
            'realisateurs.*' => 'required|integer|exists:utilisateur,idUtilisateur',
        ]);

        $tache->update([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'dateD' => $validated['dateD'],
        ]);

        // Synchroniser realisateurs (on wipe & reattach)
        $ids = $validated['realisateurs'] ?? [];
        $tache->realisateurs()->sync([]);
        if (!empty($ids)) {
            foreach ($ids as $uId) {
                $tache->realisateurs()->attach($uId, ['dateM' => null, 'description' => null]);
            }
        }

        return redirect()->route('tache.index')->with('status', __('taches.messages.updated'));
    }

    public function delete(Request $request, Tache $tache)
    {
        try {
            $tache->delete();

            return redirect()
                ->route('tache.index')
                ->with('status', __('taches.messages.deleted'));

        } catch (\Exception $e) {
            return redirect()
                ->route('tache.index')
                ->with('error', 'Erreur lors de la suppression.');
        }
    }


    public function show($id)
    {
        $tache = Tache::with(['realisateurs'])->findOrFail($id);

        $historique = TacheHistorique::with('utilisateur')
            ->where('idTache', $tache->idTache)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('tache.show', compact('tache', 'historique'));
    }

    public function markDone($id)
    {
        $tache = Tache::findOrFail($id);
        $tache->etat = 'done';
        $tache->save();

        // Fin de la tâche renseignée dans l'historique
        TacheHistorique::create([
            'idTache' => $tache->idTache,
            'statut' => __('taches.history_statuses.done'),
            'titre' => $tache->titre,
            'urgence' => $tache->type,
            'description' => __('taches.history_statuses.done_description'),
            'modifie_par' => auth()->user()->idUtilisateur ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    public function createHistorique(Tache $tache)
    {
        if ($tache->etat === 'done') {
            return to_route('tache.show', $tache)
                ->with('status', __('taches.messages.history_locked'));
        }

        // Accès réservé au CA ET aux utilisateurs assignés à la tâche
        if (
            !$tache->realisateurs->contains('idUtilisateur', auth()->user()->idUtilisateur)
            && !auth()->user()->can('gerer-tache')
        ) {
            return to_route('tache.show', $tache)
                ->with('status', __('taches.messages.history_not_allowed'));
        }

        return view('tache.historique.create', compact('tache'));
    }


    public function storeHistorique(Request $request, Tache $tache)
    {
        if ($tache->etat === "done") {
            return to_route('tache.show', $tache)->with('status', __('taches.messages.history_locked'));
        }

        // Accès réservé au CA ET aux utilisateurs assignés à la tâche
        if (
            !$tache->realisateurs->contains('idUtilisateur', auth()->user()->idUtilisateur)
            && !auth()->user()->can('gerer-tache')
        ) {
            return to_route('tache.show', $tache)
                ->with('status', __('taches.messages.history_not_allowed'));
        }

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        TacheHistorique::create([
            'idTache' => $tache->idTache,
            'statut' => __('taches.history_statuses.progress'),
            'titre' => $validated['titre'],
            'urgence' => $tache->type,
            'description' => $validated['description'] ?? null,
            'modifie_par' => auth()->user()->idUtilisateur ?? null,
        ]);

        if ($tache->etat !== "doing") {
            $tache->etat = 'doing';
            $tache->save();
        }

        return to_route('tache.show', $tache)->with('status', __('taches.messages.history_added'));
    }
}
