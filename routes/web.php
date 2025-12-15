<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\Admin\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\EtiquetteController;



Route::get('/', [ActualiteController::class, 'index'])->name('home');
Route::post('/actualites/filter', [ActualiteController::class, 'filter'])->name('actualites.filter');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


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

    Route::middleware(['permission:gerer-etiquettes'])->name('admin.')->group(function () {
        Route::resource('/pannel/etiquettes', EtiquetteController::class)->except(['show']);
        Route::get('/pannel/etiquettes/data', [EtiquetteController::class, 'data'])->name('etiquettes.data');
    });

    Route::middleware(['permission:gerer-actualites'])->name('admin.')->group(function () {
        Route::resource('actualites', ActualiteController::class)->except(['index', 'show']);
        Route::get('/pannel/actualites/data', [ActualiteController::class, 'data'])->name('actualites.data');
        Route::get('/pannel/actualites', [ActualiteController::class, 'adminIndex'])->name('actualites.index');
        Route::delete('/actualites/{idActualite}/documents/{idDocument}', [ActualiteController::class, 'detachDocument'])
            ->name('actualites.detachDocument');
    });

});

Route::get('/actualites/{id}', [ActualiteController::class, 'show'])->name('actualites.show');

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['fr', 'eus'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');


require __DIR__ . '/auth.php';
