<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgricultorController;
use App\Http\Controllers\ParcialidadController;
use App\Http\Controllers\SolicitudPesajeController;
use App\Http\Controllers\TransporteController;
use App\Http\Controllers\TransportistaController;

Route::get('/', function () {
    return response()->json(['message' => 'Hello world!']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Middleware JWT para rutas generales de autenticación
Route::middleware(['auth:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user', [AuthController::class, 'updateUser']);
});

// Rutas CRUD para administradores
Route::middleware(['auth:api'])->group(function () {
    Route::get('/agricultores', [AgricultorController::class, 'index']);
    Route::get('/agricultores/{id}', [AgricultorController::class, 'show']);
    Route::post('/agricultores', [AgricultorController::class, 'store']);
    Route::put('/agricultores/{id}', [AgricultorController::class, 'update']);
    Route::delete('/agricultores/{id}', [AgricultorController::class, 'destroy']);
});

// Rutas para el rol Agricultor
Route::prefix('agricultor')->middleware(['auth:api','es.agricultor'])->group(function () {
    Route::get('perfil', [AgricultorController::class, 'perfil']);
    Route::get('solicitudes', [AgricultorController::class, 'misSolicitudes']);
    Route::post('solicitudes', [SolicitudPesajeController::class, 'store']);
    
    // API Resources para el agricultor
    Route::apiResource('transportes', TransporteController::class);
    Route::apiResource('transportistas', TransportistaController::class);
    Route::apiResource('solicitudes-pesaje', SolicitudPesajeController::class);
    Route::apiResource('parcialidades', ParcialidadController::class);
});