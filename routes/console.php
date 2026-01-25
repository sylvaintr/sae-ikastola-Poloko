<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\FactureController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote())->hourly();
})->purpose('Display an inspiring quote');

// Schedule a monthly task that resolves the controller and calls the method.
Schedule::call(function () {
    app(FactureController::class)->createFacture();
})->monthly();
