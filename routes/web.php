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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'admin.index')->name('index');
        Route::view('/publications', 'admin.messages')->name('messages');
        Route::view('/comptes', 'admin.accounts')->name('accounts');
       Route::view('/familles', 'admin.familles.index')->name('admin.familles.index');
        Route::view('/classes', 'admin.classes')->name('classes');
        Route::view('/facture', 'admin.invoices')->name('invoices');
        Route::view('/notifications', 'admin.notifications')->name('notifications');
    });
});

Route::get('/presence', function () {
    return view('presence.index');
})->name('presence.index');

Route::get('/admin/familles', [FamilleController::class, 'index'])->name('admin.familles.index');
Route::get('/admin/familles/create', [FamilleController::class, 'create'])->name('admin.familles.create');
Route::post('/admin/familles', [FamilleController::class, 'ajouter'])->name('admin.familles.store');
Route::get('/admin/familles/{id}', [FamilleController::class, 'show'])->name('admin.familles.show');
// Route pour afficher la page create avec les données d'une famille (Mode Modif)
Route::get('/admin/familles/{id}/edit', [FamilleController::class, 'edit'])->name('admin.familles.edit');
Route::get('/api/search/users', [FamilleController::class, 'searchUsers']);
// Route AJAX pour sauvegarder la parité via LierController
Route::put('/admin/lier/update-parite', [LierController::class, 'updateParite'])->name('admin.lier.updateParite');

Route::put('/familles/{id}', [FamilleController::class, 'update'])->name('admin.familles.update');
Route::delete('/familles/{id}', [FamilleController::class, 'delete'])->name('admin.familles.delete');


Route::get('/presence/classes', [PresenceController::class, 'classes'])->name('presence.classes');
Route::get('/presence/students', [PresenceController::class, 'students'])->name('presence.students');
Route::get('/presence/status', [PresenceController::class, 'status'])->name('presence.status');
Route::post('/presence/save', [PresenceController::class, 'save'])->name('presence.save');

require __DIR__ . '/auth.php';



