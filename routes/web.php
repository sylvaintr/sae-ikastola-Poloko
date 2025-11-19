<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
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
Route::get('/familles/create', [FamilleController::class, 'createView'])->name('admin.familles.create');
Route::get('/admin/familles/{id}', [FamilleController::class, 'show'])->name('admin.familles.show');
Route::get('/familles/{id}/edit', [FamilleController::class, 'editView'])->name('admin.familles.edit');
Route::put('/familles/{id}', [FamilleController::class, 'update'])->name('admin.familles.update');
Route::delete('/familles/{id}', [FamilleController::class, 'delete'])->name('admin.familles.delete');


Route::get('/presence/classes', [PresenceController::class, 'classes'])->name('presence.classes');
Route::get('/presence/students', [PresenceController::class, 'students'])->name('presence.students');
Route::get('/presence/status', [PresenceController::class, 'status'])->name('presence.status');
Route::post('/presence/save', [PresenceController::class, 'save'])->name('presence.save');

require __DIR__ . '/auth.php';



