<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Méthode pour afficher la page de confirmation du mot de passe. Cette méthode retourne la vue "auth.confirm-password" qui contient le formulaire permettant à l'utilisateur de saisir son mot de passe pour confirmer son identité avant d'accéder à certaines fonctionnalités sensibles du site. Le formulaire inclut un champ pour le mot de passe et un bouton de soumission. Lorsque l'utilisateur soumet le formulaire, les données sont envoyées à la méthode "store" pour validation. Cette méthode est généralement utilisée pour protéger des actions critiques, comme la modification des informations de compte ou la suppression d'un compte, en s'assurant que l'utilisateur est bien celui qu'il prétend être en demandant une confirmation de son mot de passe.
     * @return View La vue du formulaire de confirmation du mot de passe
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Méthode pour gérer la soumission du formulaire de confirmation du mot de passe. Cette méthode valide le mot de passe saisi par l'utilisateur en le comparant au mot de passe stocké dans la base de données pour l'utilisateur connecté. Si la validation échoue (c'est-à-dire si le mot de passe est incorrect), une exception de validation est levée avec un message d'erreur spécifique pour le champ du mot de passe. Si la validation réussit, la session est mise à jour avec un timestamp indiquant que le mot de passe a été confirmé, et l'utilisateur est redirigé vers la page d'accueil ou vers une page précédemment intentionnée. Cette méthode est essentielle pour renforcer la sécurité des actions sensibles en s'assurant que l'utilisateur a récemment confirmé son mot de passe avant d'autoriser l'accès à ces actions.
     * @param Request $request La requête HTTP contenant les données du formulaire de confirmation du mot de passe
     * @return RedirectResponse Redirection vers la page d'accueil ou une page précédemment intentionnée si la validation réussit, ou levée d'une exception de validation si la validation échoue
     * @throws ValidationException Si la validation du mot de passe échoue
     */
    public function store(Request $request): RedirectResponse
    {
        if (
            ! Auth::guard('web')->validate([
                'email'    => $request->user()->email,
                'password' => $request->password,
            ])
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth.mot_de_passe'),
            ]);
        }

        $request->session()->put('auth.mot_de_passe_confirme_a', time());

        return redirect()->intended(route('home'));
    }
}
