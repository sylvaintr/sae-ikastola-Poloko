<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Méthode pour afficher la page de connexion. Cette méthode retourne la vue "auth.login" qui contient le formulaire de connexion permettant aux utilisateurs de s'identifier sur le site. Le formulaire inclut des champs pour l'adresse e-mail et le mot de passe, ainsi qu'un bouton de soumission. Cette méthode est généralement utilisée pour afficher la page de connexion lorsque l'utilisateur accède à l'URL correspondante ou lorsqu'une redirection vers la page de connexion est nécessaire.
     * @return View La vue du formulaire de connexion
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Méthode pour gérer la soumission du formulaire de connexion. Cette méthode utilise une requête personnalisée "LoginRequest" pour valider les données soumises par l'utilisateur, puis tente d'authentifier l'utilisateur avec les informations fournies. Si l'authentification réussit, la session est régénérée pour prévenir les attaques de fixation de session, et l'utilisateur est redirigé vers la page d'accueil ou une page précédemment intentionnée. Si l'authentification échoue, l'utilisateur est redirigé en arrière avec des erreurs de validation appropriées. Cette méthode est essentielle pour gérer le processus de connexion des utilisateurs et assurer la sécurité de la session.
     * @param LoginRequest $request La requête HTTP contenant les données du formulaire de connexion
     * @return RedirectResponse Redirection vers la page d'accueil ou une page précédemment intentionnée si l'authentification réussit, ou redirection en arrière avec des erreurs de validation si l'authentification échoue
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue ou si les informations d'identification sont incorrectes
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect("/");
    }

    /**
     * Méthode pour gérer la déconnexion de l'utilisateur. Cette méthode utilise le guard "web" pour déconnecter l'utilisateur actuellement authentifié, puis invalide la session et régénère le token CSRF pour des raisons de sécurité. Enfin, l'utilisateur est redirigé vers la page d'accueil. Cette méthode est généralement appelée lorsque l'utilisateur clique sur un lien ou un bouton de déconnexion, et elle assure que la session de l'utilisateur est correctement terminée pour prévenir tout accès non autorisé après la déconnexion.
     * @param Request $request La requête HTTP contenant les informations de la session de l'utilisateur
     * @return RedirectResponse Redirection vers la page d'accueil après la déconnexion
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(route('home'));
    }
}
