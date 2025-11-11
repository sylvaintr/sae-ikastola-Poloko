<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\LierController;

const FAMILLE_ROUTE = '/familles/{id}';

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Utilisateurs
Route::post('utilisateurs', [UtilisateurController::class, 'store']);
Route::get('utilisateurs', [UtilisateurController::class, 'searchByNom']);

// Lier
Route::post('lier', [LierController::class, 'store']);
Route::put('parite', [LierController::class, 'updateParite']);

// Familles
Route::get(FAMILLE_ROUTE, [FamilleController::class, 'show']);
Route::delete(FAMILLE_ROUTE, [FamilleController::class, 'delete']);
Route::put(FAMILLE_ROUTE, [FamilleController::class, 'update']);
Route::post('/familles', [FamilleController::class, 'ajouter']);
Route::get('familles2', [FamilleController::class, 'index']);

