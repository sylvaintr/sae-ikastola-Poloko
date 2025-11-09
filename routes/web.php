<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnfantController;
use App\Http\Controllers\FamilleController;
Route::get('/', function () {
    return view('layouts.app');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';



/*
Route::get('familles2', [FamilleController::class, 'index'])->name('familles.index');

Route::delete('/familles/{id}', [FamilleController::class, 'delete'])->name('familles.idex');

Route::post('/familles', [FamilleController::class, 'ajouter'])->name('familles.create'); 

Route::get('/familles/{id}', [FamilleController::class, 'show'])->name('familles.show');

Route::put('/familles/{id}', [FamilleController::class, 'update'])->name('familles.update');

*/