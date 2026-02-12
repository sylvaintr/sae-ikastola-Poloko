<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\FactureController;
use Illuminate\Support\Facades\DB;

// Définition de la commande 'inspire'
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Planification des tâches
Schedule::command('inspire')->hourly();

Schedule::command('notifications:check')
    ->dailyAt('08:00')
    ->timezone('Europe/Paris');

// Nettoyage automatique des notifications lues (> 15 jours)
Schedule::call(function () {
    DB::table('notifications')
        ->whereNotNull('read_at')
        ->where('read_at', '<', now()->subDays(15))
        ->delete();
})->daily();

// Génération mensuelle des factures
Schedule::call(function () {
    app(FactureController::class)->createFacture();
})->monthly();
