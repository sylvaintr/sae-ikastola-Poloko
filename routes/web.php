<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Admin\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\LierController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\EtiquetteController;

/*
|--------------------------------------------------------------------------
| Constantes pour routes récurrentes
|--------------------------------------------------------------------------
*/

if (!defined('ROUTE_ADD')) {
    define('ROUTE_ADD', '/ajouter');
    define('ROUTE_EDIT', '/modifier');
    define('ROUTE_VALIDATE', '/valider');
    define('ROUTE_ARCHIVE', '/archiver');

    define('ROUTE_CLASSE', '/{classe}');
    define('ROUTE_OBLIGATORY_DOCUMENT', '/{obligatoryDocument}');
    define('ROUTE_DEMANDE', '/{demande}');
}

Route::get('/', [ActualiteController::class, 'index'])->name('home');
Route::post('/actualites/filter', [ActualiteController::class, 'filter'])->name('actualites.filter');

Route::middleware('auth')->group(function () {

    // ---------------- Profil utilisateur ----------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/document', [ProfileController::class, 'uploadDocument'])->name('profile.document.upload');
    Route::get('/profile/document/{document}/download', [ProfileController::class, 'downloadDocument'])->name('profile.document.download');
    Route::delete('/profile/document/{document}', [ProfileController::class, 'deleteDocument'])->name('profile.document.delete');

    // ---------------- Gestion Demandes ----------------
    Route::middleware('can:access-demande')
        ->prefix('demande')
        ->name('demandes.')
        ->group(function () {
            Route::get('/', [DemandeController::class, 'index'])->name('index');
            Route::get('/create', [DemandeController::class, 'create'])->name('create');
            Route::post('/', [DemandeController::class, 'store'])->name('store');

            Route::get(ROUTE_DEMANDE, [DemandeController::class, 'show'])->name('show');
            Route::get(ROUTE_DEMANDE . '/edit', [DemandeController::class, 'edit'])->name('edit');
            Route::put(ROUTE_DEMANDE, [DemandeController::class, 'update'])->name('update');
            Route::patch(ROUTE_DEMANDE . '/valider', [DemandeController::class, 'validateDemande'])->name('validate');
            Route::delete(ROUTE_DEMANDE, [DemandeController::class, 'destroy'])->name('destroy');

            Route::get(ROUTE_DEMANDE . '/historique/ajouter', [DemandeController::class, 'createHistorique'])->name('historique.create');
            Route::post(ROUTE_DEMANDE . '/historique', [DemandeController::class, 'storeHistorique'])->name('historique.store');
        });

    // ---------------- Routes administrateur (role CA) ----------------
    Route::middleware(['role:CA'])->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {

            Route::view('/', 'admin.index')->name('index');
            Route::view('/publications', 'admin.messages')->name('messages');
            Route::view('/familles', 'admin.families')->name('families');
            Route::view('/notifications', 'admin.notifications')->name('notifications');

            // ---------------- Comptes ----------------
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
                    Route::patch("{$accountRoute}/documents/{document}/validate", 'validateDocument')->name('documents.validate');
                    Route::get("{$accountRoute}/documents/{document}/download", 'downloadDocument')->name('documents.download');
                    Route::delete("{$accountRoute}/documents/{document}", 'deleteDocument')->name('documents.delete');
                });

            // ---------------- Classes ----------------
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

            // ---------------- Documents obligatoires ----------------
            Route::prefix('documents-obligatoires')
                ->name('obligatory_documents.')
                ->controller(\App\Http\Controllers\Admin\ObligatoryDocumentController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get(ROUTE_ADD, 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get(ROUTE_OBLIGATORY_DOCUMENT . ROUTE_EDIT, 'edit')->name('edit');
                    Route::put(ROUTE_OBLIGATORY_DOCUMENT, 'update')->name('update');
                    Route::delete(ROUTE_OBLIGATORY_DOCUMENT, 'destroy')->name('destroy');
                });

            // ---------------- Factures ----------------
            Route::resource('/facture', FactureController::class);
            Route::get('/factures-data', [FactureController::class, 'facturesData'])->name('factures.data');
            Route::get('/facture/{id}/export', [FactureController::class, 'exportFacture'])->name('facture.export');
            Route::get('/facture/{id}/envoyer', [FactureController::class, 'envoyerFacture'])->name('facture.envoyer');
            Route::get('/facture/{id}/verifier', [FactureController::class, 'validerFacture'])->name('facture.valider');

            // ---------------- Ajout des routes Famille + LierController ----------------
            Route::prefix('familles')->name('familles.')->group(function () {
    Route::get('/', [FamilleController::class, 'index'])->name('index');
    Route::get('/create', [FamilleController::class, 'create'])->name('create');
    Route::post('/', [FamilleController::class, 'ajouter'])->name('store');
    Route::get('/{id}', [FamilleController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [FamilleController::class, 'edit'])->name('edit');
    Route::delete('/{id}', [FamilleController::class, 'delete'])->name('delete');
   
    
    });

           
        });
        });
        Route::get('/api/search/users', [FamilleController::class, 'searchUsers']);
 Route::put('/admin/lier/update-parite', [LierController::class, 'updateParite'])->name('admin.lier.updateParite');

        // ---------------- Présence ----------------
        Route::get('/presence', function () { return view('presence.index'); })->name('presence.index');
        Route::get('/presence/classes', [PresenceController::class, 'classes'])->name('presence.classes');
        Route::get('/presence/students', [PresenceController::class, 'students'])->name('presence.students');
        Route::get('/presence/status', [PresenceController::class, 'status'])->name('presence.status');
        Route::post('/presence/save', [PresenceController::class, 'save'])->name('presence.save');
    

    // Taches
    Route::middleware('can:access-tache')->group(function () {
        Route::get('/tache', [TacheController::class, 'index'])->name('tache.index');
        Route::get('/tache/get-datatable', [TacheController::class, 'getDatatable'])->name('tache.get-datatable');
        Route::get('/tache/{tache}/show', [TacheController::class, 'show'])->name('tache.show');
        Route::get('/tache/{tache}/historique/create', [TacheController::class, 'createHistorique'])->name('tache.historique.create');
        Route::post('/tache/{tache}/historique', [TacheController::class, 'storeHistorique'])->name('tache.historique.store');
        Route::middleware('can:gerer-tache')->group(function () {
            Route::get('/tache/create', [TacheController::class, 'create'])->name('tache.create');
            Route::post('/tache/store', [TacheController::class, 'store'])->name('tache.store');
            Route::get('/tache/{tache}/edit', [TacheController::class, 'edit'])->name('tache.edit');
            Route::put('/tache/{tache}', [TacheController::class, 'update'])->name('tache.update');
            Route::delete('/tache/{tache}', [TacheController::class, 'delete'])->name('tache.delete');
            Route::patch('/taches/{id}/done', [TacheController::class, 'markDone'])->name('tache.markDone');
        });
    });

    // Recherche des utilisateurs
    Route::get('/users/search', [UtilisateurController::class, 'search'])->name('users.search');

    // Recherche des rôles
    Route::get('/roles/search', [RoleController::class, 'search'])->name('roles.search');

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