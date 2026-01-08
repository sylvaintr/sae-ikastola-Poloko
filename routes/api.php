<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\LierController; 

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- Utilisateurs ---
Route::post('utilisateurs', [UtilisateurController::class, 'store']);
Route::get('utilisateurs', [UtilisateurController::class, 'searchByNom']);

// --- Lier (Parité) ---
Route::post('lier', [LierController::class, 'store']);
Route::put('lier/update-parite', [LierController::class, 'updateParite']);

// --- Familles ---
Route::get('search', [FamilleController::class, 'searchByParent']);
Route::get('familles', [FamilleController::class, 'index']);           
Route::post('familles', [FamilleController::class, 'ajouter']);        
Route::get('familles/{id}', [FamilleController::class, 'show']);       
Route::put('familles/{id}', [FamilleController::class, 'update']);     
Route::delete('familles/{id}', [FamilleController::class, 'delete']);