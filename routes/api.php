<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return response()->json(['message' => 'Hello world!']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user', [AuthController::class, 'updateUser']);
});


use App\Http\Controllers\AgricultorController;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/agricultores', [AgricultorController::class, 'index']);
    Route::get('/agricultores/{id}', [AgricultorController::class, 'show']);
    Route::post('/agricultores', [AgricultorController::class, 'store']);
    Route::put('/agricultores/{id}', [AgricultorController::class, 'update']);
    Route::delete('/agricultores/{id}', [AgricultorController::class, 'destroy']);
});
