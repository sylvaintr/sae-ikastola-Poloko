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

                // recherche partielle ('250' match '250', '1250' etc) :
                // $query->where('idTache', 'like', "%{$requestId}%");
            }

            return DataTables::of($query)
                ->editColumn('dateD', function ($row) {
                    return \Carbon\Carbon::parse($row->dateD)->format('d/m/Y');
                })

                ->editColumn('etat', function ($row) {

                    switch ($row->etat) {
                        case 'done':
                            return '<span class="badge bg-success px-2 py-1">Terminé</span>';

                        case 'doing':
                            return '<span class="badge bg-warning text-dark px-2 py-1">En cours</span>';

                        case 'todo':
                        default:
                            return '<span class="badge bg-orange px-2 py-1" style="background-color:#fd7e14;">En attente</span>';
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

                ->addColumn('urgence', function ($tache) {
                    return match ($tache->type) {
                        'low' => 'Faible',
                        'medium' => 'Moyenne',
                        'high' => 'Élevée',
                        default => ucfirst($tache->type),
                    };
                })

                ->addColumn('action', function ($row) {
                    $showUrl = route('tache.show', $row);
                    $editUrl = route('tache.edit', $row);
                    $deleteUrl = route('tache.delete', $row);
                    $doingUrl = route('tache.markDoing', $row->idTache);
                    $doneUrl = route('tache.markDone', $row->idTache);

                    // Si déjà terminée => désactiver la coche
                    $etat = $row->etat;

                    if ($etat === 'done') {
                        $confirmationButton = '<i class="bi bi-check-circle-fill big-icon text-success" title="Tâche terminée" style="opacity:0.5; cursor:not-allowed;"></i>';
                    }
                    elseif ($etat === 'doing') {
                        $confirmationButton = '<a href="#" class="mark-done" title="Marquer comme terminée" data-url="'.$doneUrl.'" style="color: black;">
                                <i class="bi bi-check-lg"></i>
                        </a>';
                    }
                    else {
                        $confirmationButton = '<a href="#" class="mark-doing" title="Marquer comme en cours" data-url="'.$doingUrl.'" style="color: black;">
                                <i class="bi bi-play big-icon"></i>
                        </a>';
                    }

                    return '
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="'.$showUrl.'" title="Voir plus" style="color: black;"><i class="bi bi-eye-fill"></i></a>
                            <a href="'.$editUrl.'" title="Modifier la tâche" style="color: black;"><i class="bi bi-pencil-square"></i></a>

                            <form action="'.$deleteUrl.'" method="POST" style="display:inline;">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" title="Supprimer la tâche" style="background-color: transparent; border: none; padding: 0px" onclick="return confirm(\'Supprimer cette tâche ?\')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>

                            '.$confirmationButton.'
                        </div>
                    ';
                })

                ->rawColumns(['action', 'etat'])
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

    public function delete(Tache $tache)
    {
        try {
            $tache->delete();
            return redirect()
                ->route('tache.index')
                ->with('success', 'Tâche supprimée avec succès.');
        } catch (\Exception $e) {
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

    public function markDoing($id)
    {
        $tache = Tache::findOrFail($id);
        $tache->etat = 'doing';
        $tache->save();

        return response()->json(['success' => true]);
    }

}
