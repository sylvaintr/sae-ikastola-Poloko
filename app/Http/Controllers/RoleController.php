<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Affiche la liste des rôles
     */
    public function index(): View
    {
        $roles = Role::orderBy('name')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Affiche un rôle et ses permissions
     */
    public function show(Role $role): View
    {
        $role->load('permissions');
        $permissions = \Spatie\Permission\Models\Permission::all();
        return view('admin.roles.show', compact('role', 'permissions'));
    }

    /**
     * Attache des permissions à un rôle
     */
    public function attachPermissions(Request $request, Role $role)
    {
        $permissionIds = $request->input('permissions', []);

        if (!empty($permissionIds)) {
            $permissions = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles.show', $role)
            ->with('success', 'Permissions mises à jour');
    }

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
