<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Méthode pour afficher le formulaire de demande de réinitialisation de mot de passe. Cette méthode retourne la vue "auth.forgot-password" qui contient le formulaire permettant aux utilisateurs de saisir leur adresse e-mail pour recevoir un lien de réinitialisation de mot de passe.
     * @return View La vue du formulaire de demande de réinitialisation de mot de passe
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Méthode pour gérer la soumission du formulaire de demande de réinitialisation de mot de passe. Cette méthode valide l'adresse e-mail soumise par l'utilisateur, tente d'envoyer un lien de réinitialisation de mot de passe à cette adresse, puis redirige en arrière avec un message de statut indiquant si l'envoi a réussi ou échoué. Si l'envoi du lien est réussi, un message de succès est affiché. Si l'envoi échoue (par exemple, si l'adresse e-mail n'est pas trouvée), l'utilisateur est redirigé en arrière avec une erreur spécifique pour le champ e-mail.
     * @param Request $request La requête HTTP contenant les données du formulaire de demande de réinitialisation de mot de passe
     * @return RedirectResponse Redirection en arrière avec un message de statut ou des erreurs de validation
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
