<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Méthode pour renvoyer l'e-mail de vérification à l'utilisateur. Cette méthode vérifie d'abord si l'utilisateur connecté a déjà vérifié son adresse e-mail. Si c'est le cas, il est redirigé vers la page d'accueil. Sinon, la méthode envoie un nouvel e-mail de vérification à l'utilisateur et redirige ensuite vers la page précédente avec un message indiquant que le lien de vérification a été envoyé. Cette méthode est généralement appelée lorsque l'utilisateur demande à recevoir à nouveau le lien de vérification par e-mail.
     * @param Request $request La requête HTTP contenant les informations de l'utilisateur connecté
     * @return RedirectResponse Redirection vers la page d'accueil si l'e-mail est déjà vérifié, sinon redirection en arrière avec un message indiquant que le lien de vérification a été envoyé
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
