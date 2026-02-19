<?php
namespace App\Http\Requests\Auth;

use App\Models\Utilisateur;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Méthode pour déterminer si l'utilisateur est autorisé à faire cette requête. Dans ce cas, la méthode retourne toujours "true", ce qui signifie que tous les utilisateurs sont autorisés à faire une requête de connexion. La logique d'autorisation spécifique (comme vérifier si l'utilisateur est déjà connecté ou s'il a des restrictions) peut être gérée ailleurs dans le processus d'authentification.
     * @return bool Toujours "true" pour permettre à tous les utilisateurs de faire une requête de connexion
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Méthode pour définir les règles de validation des données de la requête de connexion. Cette méthode retourne un tableau de règles de validation pour les champs "email" et "password". Le champ "email" doit être requis, de type chaîne de caractères et respecter le format d'adresse e-mail. Le champ "password" doit être requis et de type chaîne de caractères. Ces règles sont utilisées pour valider les données soumises par l'utilisateur lors de la tentative de connexion, assurant que les informations fournies sont dans le format attendu avant d'essayer d'authentifier l'utilisateur.
     * @return array Les règles de validation pour les champs de la requête de connexion
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Méthode pour tenter d'authentifier l'utilisateur avec les données de la requête. Cette méthode vérifie d'abord si la requête n'est pas limitée par le système de limitation de taux (rate limiting) pour prévenir les attaques par force brute. Ensuite, elle tente d'authentifier l'utilisateur en utilisant les informations d'identification fournies (email et mot de passe). Si l'utilisateur existe mais est archivé ou non validé, une exception de validation est levée avec un message d'erreur approprié. Si l'authentification échoue pour d'autres raisons, une exception de validation est également levée. Si l'authentification réussit, le compteur de tentatives de connexion est réinitialisé.
     * @return void
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue ou si les informations d'identification sont incorrectes, ou si le compte de l'utilisateur est archivé ou non validé
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = (string) $this->input('email');

        $user = Utilisateur::query()->where('email', $email)->first();
        if ($user && $user->isArchived()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Vérifier si le compte est validé avant de permettre la connexion
        if ($user && ! $user->statutValidation) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.account_not_validated'),
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Méthode pour vérifier si la requête n'est pas limitée par le système de limitation de taux. Cette méthode vérifie si le nombre de tentatives de connexion pour l'adresse IP et l'email de la requête dépasse la limite autorisée (5 tentatives). Si c'est le cas, elle déclenche un événement de verrouillage (Lockout) et lance une exception de validation avec un message indiquant que l'accès est temporairement bloqué. Le message d'erreur inclut le nombre de secondes et de minutes restantes avant que l'utilisateur puisse réessayer de se connecter. Si la requête n'est pas limitée, la méthode se termine sans faire quoi que ce soit, permettant à l'authentification de se poursuivre normalement.
     * @return void
     * @throws \Illuminate\Validation\ValidationException Si la requête est limitée par le système de limitation de taux, avec un message d'erreur indiquant le temps restant avant de pouvoir réessayer
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Méthode pour générer la clé de limitation de taux basée sur l'email et l'IP de la requête.
     * Cette méthode combine l'email de la requête (converti en minuscules et translittéré pour éviter les problèmes de caractères spéciaux) avec l'adresse IP de l'utilisateur pour créer une clé unique utilisée par le système de limitation de taux. Cette clé permet de suivre le nombre de tentatives de connexion pour une combinaison spécifique d'email et d'IP, ce qui aide à prévenir les attaques par force brute en limitant le nombre de tentatives autorisées avant de bloquer temporairement l'accès.
     * @return string La clé de limitation de taux pour la requête de connexion
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
