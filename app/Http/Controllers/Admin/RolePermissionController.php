<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Attache une permission à un rôle.
     */
    public function attach(Request $request, Role $role)
    {
        // Accept either a single `permission` or an array `permissions[]`
        $inputs = $request->input('permissions', $request->input('permission'));

        if (is_null($inputs) || (is_array($inputs) && count($inputs) === 0)) {
            return $this->respondNotFound($request);
        }

        if (! is_array($inputs)) {
            $inputs = [$inputs];
        }

        $attached = [];
        $notFound = [];

        foreach ($inputs as $input) {
            $permission = is_numeric($input)
                ? Permission::find($input)
                : Permission::where('name', $input)->first();

            if (! $permission) {
                $notFound[] = $input;
                continue;
            }

            // givePermissionTo is idempotent with Spatie package
            $role->givePermissionTo($permission);
            $attached[] = $permission->name;
        }

        if (count($attached) === 0) {
            return $this->respondNotFound($request);
        }

        $msg = count($attached) > 1 ? 'Permissions attachées au rôle.' : 'Permission attachée au rôle.';
        if (count($notFound) > 0) {
            $msg .= ' Certaines permissions introuvables: ' . implode(', ', $notFound) . '.';
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Détache une permission d'un rôle.
     * @param Request $request - La requête HTTP, utilisée pour déterminer le format de réponse
     * @param string|int $permission - ID ou nom de la permission à détacher
     * @param Role $role - Le rôle dont on veut détacher la permission
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function detach(Request $request, Role $role, $permission)
    {
        $perm = is_numeric($permission)
            ? Permission::find($permission)
            : Permission::where('name', $permission)->first();

        if (! $perm) {
            return $this->respondNotFound($request);
        }

        $role->revokePermissionTo($perm);

        return redirect()->back()->with('success', 'Permission supprimée du rôle.');
    }

    protected function respondNotFound(Request $request)
    {

        return redirect()->back()->withErrors(['permission' => 'Permission introuvable.']);
    }

    /**
     * Affiche la page de gestion des permissions pour un rôle.
     */
    public function show(Request $request, Role $role)
    {
        $allPermissions = Permission::orderBy('name')->get();
        return view('admin.roles.show', [
            'role'        => $role,
            'permissions' => $allPermissions,
        ]);
    }

    /**
     * Liste tous les rôles pour accéder à la gestion des permissions.
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $roles   = Role::select('idRole', 'name')->orderBy('name')->paginate($perPage)->withQueryString();
        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
