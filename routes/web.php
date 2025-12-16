<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\LierController;

Route::get('/', function () {
    return view('layouts.app');
})->name('home');

Route::middleware('auth')->group(function () {

    // --- PROFIL ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- DASHBOARD ADMIN (Vues statiques) ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'admin.index')->name('index');
        Route::view('/publications', 'admin.messages')->name('messages');
        Route::view('/comptes', 'admin.accounts')->name('accounts');
        Route::view('/classes', 'admin.classes')->name('classes');
        Route::view('/facture', 'admin.invoices')->name('invoices');
        Route::view('/notifications', 'admin.notifications')->name('notifications');
    });

    // --- GESTION DES FAMILLES (CRUD) ---
    Route::prefix('admin/familles')->name('admin.familles.')->group(function () {
        Route::get('/', [FamilleController::class, 'index'])->name('index');
        Route::get('/create', [FamilleController::class, 'create'])->name('create');
        Route::post('/', [FamilleController::class, 'ajouter'])->name('store');
        Route::get('/{id}', [FamilleController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FamilleController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [FamilleController::class, 'delete'])->name('delete');
    });

    // --- ROUTES AJAX / API INTERNES ---
    Route::get('/api/search/users', [FamilleController::class, 'searchUsers'])->name('api.search.users');
    Route::put('/admin/lier/update-parite', [LierController::class, 'updateParite'])->name('admin.lier.updateParite');

    // --- GESTION DE LA PRÉSENCE ---
    Route::get('/presence', function () {
        return view('presence.index');
    })->name('presence.index');

    Route::prefix('presence')->name('presence.')->group(function () {
        Route::get('/classes', [PresenceController::class, 'classes'])->name('classes');
        Route::get('/students', [PresenceController::class, 'students'])->name('students');
        Route::get('/status', [PresenceController::class, 'status'])->name('status');
        Route::post('/save', [PresenceController::class, 'save'])->name('save');
    });

});

require __DIR__ . '/auth.php';

