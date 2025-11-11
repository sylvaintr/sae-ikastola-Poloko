<?php

use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresenceController;
use Illuminate\Support\Facades\Route;

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
        Route::view('/familles', 'admin.families')->name('families');
        Route::prefix('classes')->name('classes.')->controller(ClasseController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/ajouter', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{classe}', 'show')->name('show');
            Route::get('/{classe}/modifier', 'edit')->name('edit');
            Route::put('/{classe}', 'update')->name('update');
            Route::delete('/{classe}', 'destroy')->name('destroy');
        });
        Route::view('/facture', 'admin.invoices')->name('invoices');
        Route::view('/notifications', 'admin.notifications')->name('notifications');
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
