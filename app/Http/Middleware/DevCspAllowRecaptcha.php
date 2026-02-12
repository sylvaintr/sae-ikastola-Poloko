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

        // CSP permissif pour le dev : autorise l'exécution du script reCAPTCHA,
        // le serveur Vite (ports 5173-5180) et les ressources locales / CDN.
        $vitePorts = '';
        for ($port = 5173; $port <= 5180; $port++) {
            $vitePorts .= "http://localhost:{$port} http://[::1]:{$port} ws://localhost:{$port} ws://[::1]:{$port} ";
        }
        $vite = trim($vitePorts);

        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' {$vite} https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "frame-src https://www.google.com/recaptcha/ https://www.google.com; "
            . "connect-src 'self' {$vite} https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' {$vite} https://fonts.googleapis.com https://fonts.bunny.net; "
            . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; "
            . "img-src 'self' data: https:;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
