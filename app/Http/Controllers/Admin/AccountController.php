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
            ->paginate(5);

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
            'roles.required' => trans('admin.common.roles_required'),
            'roles.min' => trans('admin.common.roles_required'),
            'mdp_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'mdp_confirmation.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        // Trouver le premier ID disponible
        $availableId = $this->findAvailableId();

        // Créer le compte avec l'ID disponible
        // Désactiver temporairement l'auto-increment pour permettre l'insertion manuelle de l'ID
        $account = new Utilisateur();
        $account->incrementing = false;
        $account->idUtilisateur = $availableId;
        $account->prenom = $validated['prenom'];
        $account->nom = $validated['nom'];
        $account->email = $validated['email'];
        $account->languePref = $validated['languePref'];
        $account->mdp = Hash::make($validated['mdp']);
        $account->statutValidation = $validated['statutValidation'] ?? false;
        $account->save();

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
            'roles.required' => trans('admin.common.roles_required'),
            'roles.min' => trans('admin.common.roles_required'),
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

    /**
     * Trouve le premier ID disponible dans la table utilisateur
     * Cherche les trous dans la séquence (ex: si 1,2,3,8,9 existent, retourne 4)
     */
    private function findAvailableId(): int
    {
        // Récupérer tous les IDs existants, triés et convertis en tableau pour recherche rapide
        $existingIds = Utilisateur::orderBy('idUtilisateur')
            ->pluck('idUtilisateur')
            ->toArray();

        // Si aucun ID n'existe, commencer à 1
        if (empty($existingIds)) {
            return 1;
        }

        // Convertir en Set pour recherche O(1) au lieu de O(n)
        $existingIdsSet = array_flip($existingIds);
        
        // Trouver le premier ID disponible en partant de 1
        $maxId = max($existingIds);
        
        // Parcourir de 1 jusqu'au maximum pour trouver le premier trou
        for ($id = 1; $id <= $maxId; $id++) {
            if (!isset($existingIdsSet[$id])) {
                return $id;
            }
        }

        // Si tous les IDs jusqu'au maximum sont utilisés, utiliser max + 1
        return $maxId + 1;
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

