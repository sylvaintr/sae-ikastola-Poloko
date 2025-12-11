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
            return DataTables::of(Tache::query())
                ->editColumn('dateD', function ($row) {
                    return \Carbon\Carbon::parse($row->dateD)->format('d/m/Y');
                })

                ->editColumn('etat', function ($tache) {
                    return match ($tache->etat) {
                        'todo' => 'En attente',
                        'doing' => 'En cours',
                        'done' => 'Terminé',
                        default => ucfirst($tache->etat),
                    };
                })

                ->addColumn('assignation', function ($tache) {
                    $first = $tache->realisateurs->first();

                    if (!$first) return '—';

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
                
                    return '
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <a href="'.$showUrl.'" style="color: black;"><i class="bi bi-eye-fill"></i></a>
                            <a href="'.$editUrl.'" style="color: black;"><i class="bi bi-pencil-square"></i></a>
                    
                            <form action="'.$deleteUrl.'" method="POST" style="display:inline;">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" style="border: none; padding: 0px"
                                    onclick="return confirm(\'Supprimer cette demande ?\')">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
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
        $roles = Role::orderBy('name')->get(); // si tu as un modèle Role
        return view('tache.form', compact('utilisateurs', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:low,medium,high',
            'realisateurs' => 'nullable|array',
            'realisateurs.*' => 'integer|exists:utilisateur,idUtilisateur',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:role,idRole'
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
                // ici on met dateM null ; tu peux choisir now()
                $tache->realisateurs()->attach($uId, ['dateM' => null, 'description' => null]);
            }
        }

        // gérer roles si besoin (ex : table role_user ou similaire)
        if (!empty($validated['roles'])) {
            // logique pour enregistrer les roles sélectionnés (selon ton modèle)
        }

        return redirect()->route('tache.index')->with('success', 'Tâche ajoutée avec succès.');
    }

    public function edit(Tache $tache)
    {
        // eager load realisateurs pour préremplir
        $tache->load('realisateurs');

        $utilisateurs = Utilisateur::orderBy('prenom')->limit(150)->get();
        $roles = Role::orderBy('name')->get();
        // tu peux récupérer roles sélectionnés selon ta structure
        $selectedRoles = []; // adapter

        return view('tache.form', compact('tache', 'utilisateurs', 'roles', 'selectedRoles'));
    }
    public function update(Request $request, Tache $tache)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:low,medium,high',
            'realisateurs' => 'nullable|array',
            'realisateurs.*' => 'integer|exists:utilisateur,idUtilisateur',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:role,idRole'
        ]);

        $tache->update([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
        ]);

        // Synchroniser realisateurs (on wipe & reattach)
        $ids = $validated['realisateurs'] ?? [];
        // si tu veux garder des pivot data tu peux faire plus finement
        $tache->realisateurs()->sync([]); // enlever tout d'abord
        if (!empty($ids)) {
            foreach ($ids as $uId) {
                $tache->realisateurs()->attach($uId, ['dateM' => null, 'description' => null]);
            }
        }

        // roles: idem si tu veux synchroniser

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

    public function show() {
        return "show";
    }
}
