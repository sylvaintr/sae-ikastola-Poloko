<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\EnfantController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\LierController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('familles',[FamilleController::class, 'store']);

Route::post('enfants',[EnfantController::class, 'store']);

Route::post('utilisateurs',[UtilisateurController::class, 'store']);

Route::post('lier',[LierController::class, 'store']);

Route::get('/familles/{id}', [FamilleController::class, 'show']);

Route::get('familles2',[FamilleController::class, 'index']);

Route::delete('/familles/{id}', [FamilleController::class, 'destroy']);

Route::get('utilisateurs', [UtilisateurController::class, 'searchByNom']);

Route::put('/familles/{id}', [FamilleController::class, 'update']);