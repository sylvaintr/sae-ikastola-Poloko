<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        $user = Utilisateur::create([
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $request->email,
            'mdp' => Hash::make($request->password),
            // valeurs par défaut pour champs requis
            'languePref' => $request->input('languePref', 'fr'),
            'statutValidation' => $request->input('statutValidation', true),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home'));
    }
}
