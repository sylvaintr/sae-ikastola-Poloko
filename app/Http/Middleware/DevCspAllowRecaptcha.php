<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware pour injecter un header Content-Security-Policy permissif
 * en environnement local afin de faciliter le développement avec
 * Google reCAPTCHA (script et iframe depuis google.com / gstatic.com).
 *
 * Ce middleware n'est actif que si APP_ENV === 'local'.
 */
class DevCspAllowRecaptcha
{
    /**
     * Méthode pour appliquer une politique de sécurité du contenu (CSP) permissive en environnement de développement. Cette méthode vérifie d'abord si l'environnement de l'application est 'local'. Si ce n'est pas le cas, elle laisse passer la requête sans modification. Sinon, elle applique une politique CSP qui autorise explicitement les ressources nécessaires pour Google reCAPTCHA, ainsi que les CDN jsdelivr et les services de polices en ligne, tout en limitant les sources de scripts, styles, images et polices aux domaines spécifiés. Cette approche permet de faciliter le développement avec reCAPTCHA tout en maintenant une certaine structure de sécurité.
     * @param Request $request La requête HTTP entrante
     * @param Closure $next Le prochain middleware ou contrôleur à appeler
     * @return \Illuminate\Http\Response La réponse HTTP après l'application de la politique CSP
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('app.env') !== 'local') {
            return $next($request);
        }

        $response = $next($request);

        // CSP permissif pour le dev : autorise l'exécution du script reCAPTCHA
        // et les ressources locales / CDN couramment utilisées pendant le dev.
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "frame-src 'self' https://www.google.com/recaptcha/ https://www.google.com; "
            . "connect-src 'self' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; "
            . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; "
            . "img-src 'self' data: https:;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
