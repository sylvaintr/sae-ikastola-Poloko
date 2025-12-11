<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\Admin\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\FactureController;
use App\Models\Facture;


Route::get('/', function () {
    return view('layouts.app');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('can:access-demande')
        ->prefix('demande')
        ->name('demandes.')
        ->group(function () {
            Route::get('/', [DemandeController::class, 'index'])->name('index');
            Route::get('/create', [DemandeController::class, 'create'])->name('create');
            Route::post('/', [DemandeController::class, 'store'])->name('store');

            Route::get('/{demande}', [DemandeController::class, 'show'])->name('show');
            Route::get('/{demande}/edit', [DemandeController::class, 'edit'])->name('edit');
            Route::put('/{demande}', [DemandeController::class, 'update'])->name('update');
            Route::patch('/{demande}/valider', [DemandeController::class, 'validateDemande'])->name('validate');
            Route::delete('/{demande}', [DemandeController::class, 'destroy'])->name('destroy');

            Route::get('/{demande}/historique/ajouter', [DemandeController::class, 'createHistorique'])->name('historique.create');
            Route::post('/{demande}/historique', [DemandeController::class, 'storeHistorique'])->name('historique.store');
        });

    Route::middleware(['role:CA'])->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            $accountRoute = '/{account}';
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
                Route::patch("{$accountRoute}/archiver", 'archive')->name('archive');
                Route::delete($accountRoute, 'destroy')->name('destroy');
            });
            Route::view('/familles', 'admin.families')->name('families');
            Route::view('/classes', 'admin.classes')->name('classes');
            Route::view('/notifications', 'admin.notifications')->name('notifications');
            Route::prefix('documents-obligatoires')->name('obligatory_documents.')->controller(\App\Http\Controllers\Admin\ObligatoryDocumentController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/ajouter', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{obligatoryDocument}/modifier', 'edit')->name('edit');
                Route::put('/{obligatoryDocument}', 'update')->name('update');
                Route::delete('/{obligatoryDocument}', 'destroy')->name('destroy');
            });

            Route::resource('/facture', FactureController::class);
            Route::get('/factures-data', [FactureController::class, 'facturesData'])->name('factures.data');
            Route::get('/facture/{id}/export', [FactureController::class, 'exportFacture'])->name('facture.export');
            Route::get('/facture/{id}/envoyer', [FactureController::class, 'envoyerFacture'])->name('facture.envoyer');
            Route::get('/facture/{id}/verifier', [FactureController::class, 'validerFacture'])->name('facture.valider');
        });

        Route::get('/presence', function () {
            return view('presence.index');
        })->name('presence.index');

        Route::get('/presence/classes', [PresenceController::class, 'classes'])->name('presence.classes');
        Route::get('/presence/students', [PresenceController::class, 'students'])->name('presence.students');
        Route::get('/presence/status', [PresenceController::class, 'status'])->name('presence.status');
        Route::post('/presence/save', [PresenceController::class, 'save'])->name('presence.save');
    });
});

require __DIR__ . '/auth.php';
