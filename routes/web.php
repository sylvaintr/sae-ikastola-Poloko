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


Route::get('familles', [FamilleController::class, 'index'])->name('familles.index'); 
Route::get('familles/{id}', [FamilleController::class, 'show'])->name('familles.show'); 

Route::get('familles/create', [FamilleController::class, 'create'])->name('familles.create'); 
Route::post('familles', [FamilleController::class, 'store'])->name('familles.store'); 

Route::get('familles/{id}/edit', [FamilleController::class, 'edit'])->name('familles.edit'); 
Route::put('familles/{id}', [FamilleController::class, 'update'])->name('familles.update'); 

Route::delete('familles/{id}', [FamilleController::class, 'destroy'])->name('familles.destroy'); 


*/