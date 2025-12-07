<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Utilisateur;
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
        return "create";
    }

    public function store()
    {
        return "store";
    }

    public function edit()
    {
        return "edit";
    }

    public function update()
    {
        return "update";
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
