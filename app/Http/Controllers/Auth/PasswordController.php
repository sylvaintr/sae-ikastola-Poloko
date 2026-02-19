<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Méthode pour mettre à jour le mot de passe de l'utilisateur. Cette méthode valide les données soumises par l'utilisateur, vérifie que le mot de passe actuel est correct, puis met à jour le mot de passe dans la base de données. Enfin, elle redirige vers la page précédente avec un message indiquant que le mot de passe a été mis à jour.
     * @param Request $request La requête HTTP contenant les données du formulaire de mise à jour du mot de passe
     * @return RedirectResponse Redirection vers la page précédente avec un message indiquant que le mot de passe a été mis à jour
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'mdp' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
