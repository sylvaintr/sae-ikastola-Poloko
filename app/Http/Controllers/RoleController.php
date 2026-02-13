<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Recherche des rôles pour l'assignation des tâches
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $roles = Role::query()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->get(['idRole', 'name']);

        return response()->json($roles);
    }
}
