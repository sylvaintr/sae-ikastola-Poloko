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
            . "frame-src https://www.google.com/recaptcha/ https://www.google.com; "
            . "connect-src 'self' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; "
            . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; "
            . "img-src 'self' data: https:;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
