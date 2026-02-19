<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Méthode pour vérifier l'adresse e-mail de l'utilisateur. Si l'e-mail est déjà vérifié, redirige vers la page d'accueil avec un paramètre "verified=1". Si l'e-mail n'est pas encore vérifié, marque l'e-mail comme vérifié, déclenche l'événement "Verified", puis redirige également vers la page d'accueil avec le même paramètre. Cette méthode est généralement appelée lorsque l'utilisateur clique sur le lien de vérification dans l'e-mail envoyé par le système.
     * @param EmailVerificationRequest $request La requête de vérification d'e-mail contenant les informations de l'utilisateur
     * @return RedirectResponse Redirection vers la page d'accueil avec un paramètre indiquant que l'e-mail a été vérifié
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home') . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('home') . '?verified=1');
    }
}
