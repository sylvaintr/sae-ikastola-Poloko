<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteMiddlewareTest extends TestCase
{
    /**
     * Ensure routes related to factures/admin are protected by auth and role:CA.
     * This is a lightweight check to detect missing middleware on sensitive routes.
     */
    public function test_facture_and_admin_routes_have_auth_and_role_middlewares()
    {
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $uri = $route->uri();

            // Focus on invoice and admin paths
            if (Str::startsWith($uri, 'admin/') || Str::contains($uri, 'facture') || Str::contains($uri, 'factures-data')) {
                $middleware = $route->gatherMiddleware();

                $hasAuth = in_array('auth', $middleware, true) || collect($middleware)->contains(fn($m) => Str::contains($m, 'auth'));
                $hasRoleCA = collect($middleware)->contains(fn($m) => Str::contains($m, 'role:CA'));

                $this->assertTrue($hasAuth, "Route [{$uri}] must have 'auth' middleware. Found: " . json_encode($middleware));
                $this->assertTrue($hasRoleCA, "Route [{$uri}] must have 'role:CA' middleware. Found: " . json_encode($middleware));
            }
        }
    }
}
