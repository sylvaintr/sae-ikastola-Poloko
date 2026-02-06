<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\FactureController;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('notifications:check')
    ->dailyAt('08:00')
    ->timezone('Europe/Paris');

/**
 * Nettoyage automatique de la base de données.
 * Supprime les notifications lues il y a plus de 15 jours.
 * Se lance tous les jours à minuit.
 */
Schedule::call(function () {
    DB::table('notifications')
        ->whereNotNull('read_at')
        ->where('read_at', '<', now()->subDays(15))
        ->delete();
})->daily();

// Schedule a monthly task that resolves the controller and calls the method.
Schedule::call(function () {
    app(FactureController::class)->createFacture();
})->monthly();
