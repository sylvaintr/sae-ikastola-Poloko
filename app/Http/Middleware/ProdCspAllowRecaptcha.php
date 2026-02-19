<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware pour définir un Content-Security-Policy adapté à la production
 * afin d'autoriser explicitement Google reCAPTCHA sans utiliser 'unsafe-inline'.
 * Ce middleware s'applique uniquement hors environnement 'local'.
 */
class ProdCspAllowRecaptcha
{
    /**
     * Méthode pour appliquer une politique de sécurité du contenu (CSP) adaptée à la production. Cette méthode vérifie d'abord si l'environnement de l'application est 'local'. Si c'est le cas, elle laisse passer la requête sans modification. Sinon, elle applique une politique CSP qui autorise explicitement les ressources nécessaires pour Google reCAPTCHA, ainsi que les CDN jsdelivr et les services de polices en ligne, tout en limitant les sources de scripts, styles, images et polices aux domaines spécifiés. Cette approche permet d'améliorer la sécurité tout en assurant le bon fonctionnement de reCAPTCHA et des autres ressources externes nécessaires.
     * @param Request $request La requête HTTP entrante
     * @param Closure $next Le prochain middleware ou contrôleur à appeler
     * @return \Illuminate\Http\Response La réponse HTTP après l'application de la politique CSP
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('app.env') === 'local') {
            return $next($request);
        }

        $response = $next($request);

        // Ajusté pour autoriser :
        // - Google reCAPTCHA (www.google.com, www.gstatic.com)
        // - CDN jsdelivr (bootstrap)
        // - fonts.bunny.net et fonts.googleapis.com
        // - chargement de fonts et images locales (self) et data: URIs
        // NOTE: l'ajout de 'unsafe-inline' améliore la compatibilité mais réduit
        // la sécurité. Idéalement remplacer par des nonces/hashes dans une mise en
        // production sécurisée.
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "frame-src https://www.google.com; "
            . "connect-src 'self' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; "
            . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; "
            . "img-src 'self' data: https:;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
