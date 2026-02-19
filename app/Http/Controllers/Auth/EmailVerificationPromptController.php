<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Méthode pour afficher la page de vérification d'e-mail. Cette méthode vérifie si l'utilisateur connecté a déjà vérifié son adresse e-mail. Si c'est le cas, il est redirigé vers la page d'accueil. Sinon, la vue "auth.verify-email" est retournée, invitant l'utilisateur à vérifier son adresse e-mail. Cette méthode est généralement utilisée pour afficher une page de rappel aux utilisateurs qui n'ont pas encore vérifié leur adresse e-mail après s'être connectés.
     * @param Request $request La requête HTTP contenant les informations de l'utilisateur connecté
     * @return RedirectResponse|View Redirection vers la page d'accueil si l'e-mail est déjà vérifié, sinon affichage de la vue de vérification d'e-mail
     */
    public function __invoke(Request $request): RedirectResponse | View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('home', absolute : false))
            : view('auth.verify-email');
    }
}
