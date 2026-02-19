<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Méthode qui attache une permission à un rôle.
     * @param Request $request La requête HTTP contenant les données de la permission à attacher.
     * @param Role $role Le rôle auquel la permission doit être attachée.
     * @return RedirectResponse Redirige en arrière avec un message de succès ou d'erreur dans la session.
     */
    public function attach(Request $request, Role $role): RedirectResponse
    {
        // Accept either a single `permission` or an array `permissions[]`
        $inputs = $request->input('permissions', $request->input('permission'));

        // If no inputs provided, interpret this as clearing all permissions for the role.
        if (is_null($inputs) || (is_array($inputs) && count($inputs) === 0)) {
            $role->syncPermissions([]);
            return redirect()->back()->with('success', 'admin.all_permissions_removed');
        }

        if (! is_array($inputs)) {
            $inputs = [$inputs];
        }

        $attached           = [];
        $notFound           = [];
        $foundPermissionIds = [];

        foreach ($inputs as $input) {
            $permission = is_numeric($input)
                ? Permission::find($input)
                : Permission::where('name', $input)->first();

            if (! $permission) {
                $notFound[] = $input;
                continue;
            }

            // givePermissionTo is idempotent with Spatie package
            $foundPermissionIds[] = $permission->id;
            $attached[]           = $permission->name;
        }

        if (empty($attached)) {
            return $this->respondNotFound();
        }

        $role->syncPermissions($foundPermissionIds);

        $msg = 'admin.permissions_attached';
        if (! empty($notFound)) {
            $msg = 'admin.some_permissions_not_found';
        }

        return redirect()->back()->with(empty($notFound) ? 'success' : 'error', $msg);

    }

    /**
     *  Méthode qui retourne une réponse d'erreur lorsque la permission n'est pas trouvée.
     * @return RedirectResponse pour rediriger en arrière avec un message d'erreur dans la session.
     */
    protected function respondNotFound(): RedirectResponse
    {

        return redirect()->back()->withErrors(['permission' => __('admin.attach_permission_not_found')]);
    }

    /**
     * Méthode qui affiche la page de gestion des permissions pour un rôle.
     * @param Role $role Le rôle pour lequel afficher les permissions.
     * @return View La vue avec les données du rôle et des permissions
     */
    public function show(Role $role): View
    {
        $allPermissions = Permission::orderBy('name')->get();
        return view('admin.roles.show', [
            'role'        => $role,
            'permissions' => $allPermissions,
        ]);
    }

    /**
     * Méthode qui liste tous les rôles pour accéder à la gestion des permissions.
     * @return View La vue avec la liste des rôles
     */
    public function index(): View
    {
        $perPage = 10;
        $roles   = Role::select('idRole', 'name')->orderBy('name')->paginate($perPage)->withQueryString();
        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
