<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Méthode pour afficher le formulaire de réinitialisation de mot de passe. Cette méthode retourne la vue "auth.reset-password" qui contient le formulaire permettant aux utilisateurs de saisir un nouveau mot de passe après avoir cliqué sur un lien de réinitialisation envoyé par e-mail. Le formulaire inclut des champs pour l'adresse e-mail, le nouveau mot de passe, la confirmation du mot de passe et un champ caché pour le token de réinitialisation. Les données de la requête sont passées à la vue pour être utilisées dans le formulaire.
     * @param Request $request La requête HTTP contenant les données nécessaires pour afficher le formulaire de réinitialisation de mot de passe
     * @return View La vue du formulaire de réinitialisation de mot de passe
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Méthode pour gérer la soumission du formulaire de réinitialisation de mot de passe. Cette méthode valide les données soumises par l'utilisateur, tente de réinitialiser le mot de passe en utilisant le token fourni, puis redirige vers la page de connexion avec un message de statut indiquant si la réinitialisation a réussi ou échoué. Si la réinitialisation est réussie, un message de succès est affiché. Si la réinitialisation échoue (par exemple, si le token est invalide ou expiré), l'utilisateur est redirigé en arrière avec une erreur spécifique pour le champ e-mail.
     * @param Request $request La requête HTTP contenant les données du formulaire de réinitialisation de mot de passe
     * @return RedirectResponse Redirection vers la page de connexion avec un message de statut ou redirection en arrière avec des erreurs de validation
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.min'       => __('auth.password_rule_length'),
            'password.confirmed' => __('auth.password_match_no'),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Utilisateur $user) use ($request) {
                $user->forceFill([
                    'mdp'            => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
