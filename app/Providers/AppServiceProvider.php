<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\Paginator;
use App\Models\Utilisateur;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(125);

        Route::bind('account', function ($value) {
            return Utilisateur::where('idUtilisateur', $value)->firstOrFail();
        });

        // Utiliser Bootstrap 5 pour la pagination
        Paginator::useBootstrapFive();
    }
}
