<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\Admin\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ClasseController;

/*
|--------------------------------------------------------------------------
| Constantes pour routes récurrentes
|--------------------------------------------------------------------------
*/

const ROUTE_ADD     = '/ajouter';
const ROUTE_EDIT    = '/modifier';
const ROUTE_VALIDATE = '/valider';
const ROUTE_ARCHIVE = '/archiver';
const ROUTE_CLASSE = '/{classe}';


Route::get('/', function () {
    return view('layouts.app');
})->name('home');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profil utilisateur
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Routes administrateur (role CA)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:CA'])->group(function () {

        Route::prefix('admin')->name('admin.')->group(function () {

            Route::view('/', 'admin.index')->name('index');
            Route::view('/publications', 'admin.messages')->name('messages');
            Route::view('/familles', 'admin.families')->name('families');
            Route::view('/notifications', 'admin.notifications')->name('notifications');


            /*
            |--------------------------------------------------------------------------
            | Comptes administratifs (AccountController)
            |--------------------------------------------------------------------------
            */
            Route::prefix('comptes')->name('accounts.')->controller(AccountController::class)
                ->group(function () {

                    $accountRoute = '/{account}';

                    Route::get('/', 'index')->name('index');
                    Route::get(ROUTE_ADD, 'create')->name('create');
                    Route::post('/', 'store')->name('store');

                    Route::get($accountRoute, 'show')->name('show');
                    Route::get("{$accountRoute}" . ROUTE_EDIT, 'edit')->name('edit');

                    Route::put($accountRoute, 'update')->name('update');
                    Route::patch("{$accountRoute}" . ROUTE_VALIDATE, 'validateAccount')->name('validate');
                    Route::patch("{$accountRoute}" . ROUTE_ARCHIVE, 'archive')->name('archive');

                    Route::delete($accountRoute, 'destroy')->name('destroy');
                });


            /*
            |--------------------------------------------------------------------------
            | Classes (ClasseController)
            |--------------------------------------------------------------------------
            */
            Route::prefix('classes')->name('classes.')->controller(ClasseController::class)
                ->group(function () {

                    Route::get('/', 'index')->name('index');
                    Route::get('/data', 'data')->name('data');

                    Route::get(ROUTE_ADD, 'create')->name('create');
                    Route::post('/', 'store')->name('store');

                    Route::get(ROUTE_CLASSE . ROUTE_EDIT, 'edit')->name('edit');
                    Route::put(ROUTE_CLASSE, 'update')->name('update');
                    Route::delete(ROUTE_CLASSE, 'destroy')->name('destroy');

                    Route::get(ROUTE_CLASSE, 'show')->name('show');
                });



            /*
            |--------------------------------------------------------------------------
            | Documents obligatoires
            |--------------------------------------------------------------------------
            */
            Route::prefix('documents-obligatoires')
                ->name('obligatory_documents.')
                ->controller(\App\Http\Controllers\Admin\ObligatoryDocumentController::class)
                ->group(function () {

                    Route::get('/', 'index')->name('index');
                    Route::get(ROUTE_ADD, 'create')->name('create');
                    Route::post('/', 'store')->name('store');

                    Route::get('/{obligatoryDocument}' . ROUTE_EDIT, 'edit')->name('edit');
                    Route::put('/{obligatoryDocument}', 'update')->name('update');
                    Route::delete('/{obligatoryDocument}', 'destroy')->name('destroy');
                });


            /*
            |--------------------------------------------------------------------------
            | Factures
            |--------------------------------------------------------------------------
            */
            Route::resource('/facture', FactureController::class);

            Route::get('/factures-data', [FactureController::class, 'facturesData'])
                ->name('factures.data');

            Route::get('/facture/{id}/export', [FactureController::class, 'exportFacture'])
                ->name('facture.export');

            Route::get('/facture/{id}/envoyer', [FactureController::class, 'envoyerFacture'])
                ->name('facture.envoyer');

            Route::get('/facture/{id}/verifier', [FactureController::class, 'validerFacture'])
                ->name('facture.valider');
        });


        /*
        |--------------------------------------------------------------------------
        | Présence
        |--------------------------------------------------------------------------
        */
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
