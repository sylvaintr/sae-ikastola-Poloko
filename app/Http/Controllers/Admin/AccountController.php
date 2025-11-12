<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = Utilisateur::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $accounts = $query->select('idUtilisateur', 'prenom', 'nom', 'email', 'statutValidation')
            ->orderBy('idUtilisateur')
            ->get();

        return view('admin.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.accounts.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom' => ['required', 'string', 'max:15'],
            'nom' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'unique:utilisateur,email'],
            'languePref' => ['required', 'string', 'max:17'],
            'mdp' => ['required', 'string', 'min:8'],
            'mdp_confirmation' => ['required', 'string', 'same:mdp'],
            'statutValidation' => ['nullable', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:role,idRole'],
        ], [
            'roles.required' => 'Au moins un rôle doit être sélectionné.',
            'roles.min' => 'Au moins un rôle doit être sélectionné.',
            'mdp_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'mdp_confirmation.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        $account = Utilisateur::create([
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'languePref' => $validated['languePref'],
            'mdp' => Hash::make($validated['mdp']),
            'statutValidation' => $validated['statutValidation'] ?? false,
        ]);

        // Sync roles with model_type automatically set
        $rolesToSync = [];
        foreach ($validated['roles'] as $roleId) {
            $rolesToSync[$roleId] = ['model_type' => Utilisateur::class];
        }
        $account->rolesCustom()->sync($rolesToSync);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.created'));
    }

    public function show(Utilisateur $account): View
    {
        $account->load(['rolesCustom' => function($query) {
            $query->select('role.idRole', 'role.name');
        }]);

        return view('admin.accounts.show', compact('account'));
    }

    public function edit(Utilisateur $account): View
    {
        $account->load(['rolesCustom' => function($query) {
            $query->select('role.idRole', 'role.name');
        }]);
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.accounts.edit', compact('account', 'roles'));
    }

    public function update(Request $request, Utilisateur $account): RedirectResponse
    {
        $rules = [
            'prenom' => ['required', 'string', 'max:15'],
            'nom' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'unique:utilisateur,email,' . $account->idUtilisateur . ',idUtilisateur'],
            'languePref' => ['required', 'string', 'max:17'],
            'mdp' => ['nullable', 'string', 'min:8'],
            'statutValidation' => ['nullable', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:role,idRole'],
        ];
        
        // Si un mot de passe est fourni, la confirmation est requise
        if ($request->filled('mdp')) {
            $rules['mdp_confirmation'] = ['required', 'string', 'same:mdp'];
        }
        
        $validated = $request->validate($rules, [
            'roles.required' => 'Au moins un rôle doit être sélectionné.',
            'roles.min' => 'Au moins un rôle doit être sélectionné.',
            'mdp_confirmation.required' => 'La confirmation du mot de passe est requise lorsque vous modifiez le mot de passe.',
            'mdp_confirmation.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        $updateData = [
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'languePref' => $validated['languePref'],
            'statutValidation' => $validated['statutValidation'] ?? false,
        ];

        if (!empty($validated['mdp'])) {
            $updateData['mdp'] = Hash::make($validated['mdp']);
        }

        $account->update($updateData);

        // Sync roles with model_type automatically set
        $rolesToSync = [];
        foreach ($validated['roles'] as $roleId) {
            $rolesToSync[$roleId] = ['model_type' => Utilisateur::class];
        }
        $account->rolesCustom()->sync($rolesToSync);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.updated'));
    }

    public function validateAccount(Utilisateur $account): RedirectResponse
    {
        $account->update(['statutValidation' => true]);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.validated'));
    }

    public function destroy(Utilisateur $account): RedirectResponse
    {
        $account->delete();

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', trans('admin.accounts_page.messages.deleted'));
    }
}

