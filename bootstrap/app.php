<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web([
            SetLocale::class,
            App\Http\Middleware\DevCspAllowRecaptcha::class,
            App\Http\Middleware\ProdCspAllowRecaptcha::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (UnauthorizedException $e, $request) {
            // Si c’est une requête API → on renvoie du JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Accès refusé : vous n'avez pas la permission requise."
                ], 403);
            }

            logger()->error($e->getMessage(), [
                'exception' => $e,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => optional($request->user())->id,
            ]);

            // Sinon, on redirige avec un message flash
            return redirect()
                ->route('home') // ou une autre route
                ->with('error', "auth.accesrefuse");
        });
        $exceptions->render(function (AuthenticationException $e, $request) {
            // Si API → réponse JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Vous devez être connecté pour accéder à cette ressource."
                ], 401);
            }

            // Enregistrer l'exception dans les logs
            logger()->error($e->getMessage(), [
                'exception' => $e,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => optional($request->user())->id,
            ]);

            // Sinon → redirection avec message flash
            return redirect()
                ->route('login') // route vers la page de connexion
                ->with('error', "auth.nonconnecte");
        });
    })->create();
