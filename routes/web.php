<?php

use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\Admin\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
Route::get('/', function () {
    return view('layouts.app');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    $classeRoute = '/{classe}';
    $accountRoute = '/{account}';

    Route::prefix('admin')->name('admin.')->group(function () use ($classeRoute, $accountRoute) {
        Route::view('/', 'admin.index')->name('index');
        Route::view('/publications', 'admin.messages')->name('messages');
        // Routes gérées par AccountController pour la gestion des comptes
        Route::prefix('comptes')->name('accounts.')->controller(AccountController::class)->group(function () use ($accountRoute) {
            Route::get('/', 'index')->name('index');
            Route::get('/ajouter', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get($accountRoute, 'show')->name('show');
            Route::get("{$accountRoute}/modifier", 'edit')->name('edit');
            Route::put($accountRoute, 'update')->name('update');
            Route::patch("{$accountRoute}/valider", 'validateAccount')->name('validate');
            Route::delete($accountRoute, 'destroy')->name('destroy');
        });
        Route::view('/familles', 'admin.families')->name('families');
        Route::prefix('classes')->name('classes.')->controller(ClasseController::class)->group(function () use ($classeRoute) {
            Route::get('/', 'index')->name('index');
            Route::get('/ajouter', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get($classeRoute, 'show')->name('show');
            Route::get("{$classeRoute}/modifier", 'edit')->name('edit');
            Route::put($classeRoute, 'update')->name('update');
            Route::delete($classeRoute, 'destroy')->name('destroy');
        });
        Route::view('/facture', 'admin.invoices')->name('invoices');
        Route::view('/notifications', 'admin.notifications')->name('notifications');
        Route::prefix('documents-obligatoires')->name('obligatory_documents.')->controller(\App\Http\Controllers\Admin\ObligatoryDocumentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/ajouter', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{obligatoryDocument}/modifier', 'edit')->name('edit');
            Route::put('/{obligatoryDocument}', 'update')->name('update');
            Route::delete('/{obligatoryDocument}', 'destroy')->name('destroy');
        });
    });
});

Route::get('/presence', function () {
    return view('presence.index');
})->name('presence.index');


Route::get('/presence/classes', [PresenceController::class, 'classes'])->name('presence.classes');
Route::get('/presence/students', [PresenceController::class, 'students'])->name('presence.students');
Route::get('/presence/status', [PresenceController::class, 'status'])->name('presence.status');
Route::post('/presence/save', [PresenceController::class, 'save'])->name('presence.save');

require __DIR__ . '/auth.php';



