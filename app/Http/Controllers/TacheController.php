<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TacheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tache.index');
    }

    public function getDatatable(Request $request)
    {
        if($request->ajax()){

            // base query
            $query = Tache::query();

            // si un Request ID est fourni, filtrer dessus
            $requestId = $request->get('request_id');
            if (!empty($requestId)) {
                // recherche exacte (typique pour un ID)
                $query->where('idTache', $requestId);
            }

            // filtre statut
            if ($request->filled('etat')) {
                $query->where('etat', $request->etat);
            }

            // filtre urgence (si tu veux séparer plus tard)
            if ($request->filled('urgence')) {
                $query->where('type', $request->urgence);
            }

            // filtre date min
            if ($request->filled('date_min')) {
                $query->whereDate('dateD', '>=', $request->date_min);
            }

            // filtre date max
            if ($request->filled('date_max')) {
                $query->whereDate('dateD', '<=', $request->date_max);
            }


            return DataTables::of($query)
                ->editColumn('dateD', function ($row) {
                    return \Carbon\Carbon::parse($row->dateD)->format('d/m/Y');
                })

                ->editColumn('etat', function ($row) {

                    switch ($row->etat) {
                        case 'done':
                            return 'Terminé';

                        case 'doing':
                            return 'En cours';

                        case 'todo':
                        default:
                            return 'En attente';
                    }
                })


                ->addColumn('assignation', function ($tache) {
                    $first = $tache->realisateurs->first();

                    if (!$first)
                    {
                        return '—';
                    }

                    return $first->prenom . ' ' . strtoupper(substr($first->nom, 0, 1)) . '.';
                })

                ->addColumn('urgence', function ($row) {
                    switch ($row->type) {
                        case 'low':
                            return 'Faible';

                        case 'medium':
                            return 'Moyenne';

                        case 'high':
                        default:
                            return 'Élevée';
                    }
                })

                ->addColumn('action', function ($row) {
                    $showUrl = route('tache.show', $row);
                    $editUrl = route('tache.edit', $row);
                    $deleteUrl = route('tache.delete', $row);
                    $doneUrl = route('tache.markDone', $row->idTache);

                    // Si déjà terminée => désactiver la coche
                    $etat = $row->etat;

                    if ($etat === 'done') {
                        $confirmationButton = '<i class="bi bi-check-circle-fill demande-action-btn big-icon text-success" title="Tâche terminée" style="opacity: 0.5; cursor:not-allowed;"></i>';
                    }
                    else {
                        $confirmationButton = '<a href="#" class="mark-done demande-action-btn text-success" title="Marquer comme terminée" data-url="'.$doneUrl.'" style="color: black;">
                            <i class="bi bi-check-lg"></i>
                        </a>';
                    }

                    return '
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="'.$showUrl.'" title="Voir plus" class="demande-action-btn"><i class="bi bi-eye"></i></a>
                            <a href="'.$editUrl.'" title="Modifier la tâche" class="demande-action-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706l-1 1a.5.5 0 0 1-.708 0L13 2.207l1-1a.5.5 0 0 1 .707 0l.795.733z"></path>
                                    <path d="M13.5 3.207L6 10.707V13h2.293l7.5-7.5L13.5 3.207zm-10 8.647V14h2.146l8.147-8.146-2.146-2.147L3.5 11.854z"></path>
                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 1,00000 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"></path>
                                </svg>
                            </a>

                            <a href="#"
                            class="delete-tache demande-action-btn"
                            data-url="'.$deleteUrl.'"
                            title="Supprimer la tâche"
                            style="color:black;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5"></path>
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM14.5 2h-13v1h13z"></path>
                                </svg>
                            </a>

                            '.$confirmationButton.'
                        </div>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('tache.index');
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
            'realisateurs' => 'nullable|array',
            'realisateurs.*' => 'integer|exists:utilisateur,idUtilisateur'
        ]);

        $tache = Tache::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'etat' => 'todo',
            'dateD' => now(),
        ]);

        // attacher realisateurs (pivot)
        if (!empty($validated['realisateurs'])) {
            foreach ($validated['realisateurs'] as $uId) {
                $tache->realisateurs()->attach($uId, ['dateM' => now(), 'description' => null]);
            }
        }

        return redirect()->route('tache.index')->with('success', 'Tâche ajoutée avec succès.');
    }

    public function edit(Tache $tache)
    {
        // eager load realisateurs pour préremplir
        $tache->load('realisateurs');

        $utilisateurs = Utilisateur::orderBy('prenom')->limit(150)->get();

        return view('tache.form', compact('tache', 'utilisateurs'));
    }

    public function update(Request $request, Tache $tache)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:low,medium,high',
            'realisateurs' => 'nullable|array',
            'realisateurs.*' => 'integer|exists:utilisateur,idUtilisateur',
        ]);

        $tache->update([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
        ]);

        // Synchroniser realisateurs (on wipe & reattach)
        $ids = $validated['realisateurs'] ?? [];
        $tache->realisateurs()->sync([]);
        if (!empty($ids)) {
            foreach ($ids as $uId) {
                $tache->realisateurs()->attach($uId, ['dateM' => null, 'description' => null]);
            }
        }

        return redirect()->route('tache.index')->with('success', 'Tâche mise à jour.');
    }

    public function delete(Request $request, Tache $tache)
    {
        try {
            $tache->delete();

            // si appel AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tâche supprimée avec succès.'
                ]);
            }

            // fallback normal (non AJAX)
            return redirect()
                ->route('tache.index')
                ->with('success', 'Tâche supprimée avec succès.');

        } catch (\Exception $e) {

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression.'
                ], 500);
            }

            return redirect()
                ->route('tache.index')
                ->with('error', 'Erreur lors de la suppression.');
        }
    }


    public function show($id)
    {
        $tache = Tache::with(['realisateurs'])->findOrFail($id);

        return view('tache.show', compact('tache'));
    }

    public function markDone($id)
    {
        $tache = Tache::findOrFail($id);
        $tache->etat = 'done';
        $tache->save();

        return response()->json(['success' => true]);
    }

}
