<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Accept either a single 'name' (compatibility) or prenom+nom
            'name' => ['required_without:prenom', 'string', 'max:255'],
            'prenom' => ['required_without:name', 'string', 'max:255'],
            'nom' => ['required_without:name', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . Utilisateur::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'languePref' => ['nullable', 'string', 'max:10'],
        ]);

        // Map 'name' -> prenom/nom when provided
        $prenom = $request->input('prenom');
        $nom = $request->input('nom');
        if (!$prenom && $request->filled('name')) {
            $parts = preg_split('/\s+/', trim($request->input('name')), 2);
            $prenom = $parts[0] ?? null;
            $nom = $parts[1] ?? '';
        }

        // Créer l'utilisateur avec statutValidation à false (non validé)
        $user = Utilisateur::create([
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $request->email,
            'mdp' => Hash::make($request->password),
            'languePref' => $request->input('languePref', 'fr'),
            'statutValidation' => false, // Toujours non validé pour les inscriptions publiques
        ]);

        // Assigner automatiquement le rôle "parent"
        $parentRole = Role::where('name', 'parent')->first();
        if ($parentRole) {
            $user->rolesCustom()->sync([
                $parentRole->idRole => ['model_type' => Utilisateur::class]
            ]);
        }

        event(new Registered($user));

        // Ne pas connecter l'utilisateur automatiquement car le compte n'est pas validé
        // Rediriger vers la page de connexion avec un message de confirmation
        return redirect(route('login'))
            ->with('status', __('auth.registration_pending_validation'));
    }
}
