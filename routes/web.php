<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('layouts.app');
//})->name('home');

Route::get('/', [ActualiteController::class, 'index'])->name('home');
Route::middleware('permission:access-gestion-actualite')->group(function () {
    Route::get('/actualite/create', [ActualiteController::class, 'create'])->name('actualites.create');
    Route::post('/actualite/store', [ActualiteController::class, 'store'])->name('actualites.store');
    Route::get('/actualite/{actualite}/edit', [ActualiteController::class, 'edit'])->name('actualites.edit');
    Route::put('/actualite/{actualite}', [ActualiteController::class, 'update'])->name('actualites.update');
    Route::delete('/actualite/{actualite}', [ActualiteController::class, 'delete'])->name('actualites.delete');    
});

// Changer de langue
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['fr', 'eus'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
