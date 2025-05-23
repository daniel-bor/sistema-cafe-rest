<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PesajeController;
use App\Http\Controllers\BeneficioController;
use App\Http\Controllers\PesoCabalController;
use App\Http\Controllers\AgricultorController;
use App\Http\Controllers\TransporteController;
use App\Http\Controllers\ParcialidadController;
use App\Http\Controllers\TransportistaController;
use App\Http\Controllers\SolicitudPesajeController;

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
Route::prefix('agricultor')->middleware(['auth:api'])->group(function () {
    Route::get('perfil', [AgricultorController::class, 'perfil']);
   

    // API Resources para el agricultor
    Route::apiResource('transportes', TransporteController::class);
    Route::apiResource('transportistas', TransportistaController::class);

    Route::apiResource('pesajes', PesajeController::class);
    Route::apiResource('parcialidades', ParcialidadController::class);
    
});

Route::prefix('beneficio')->middleware(['auth:api'])->group(function () {
    Route::get('perfil', [BeneficioController::class, 'perfil']);
    Route::get('parcialidades/pendientes', [BeneficioController::class, 'parcialidadesPendientes']);
    Route::post('parcialidades/{id}/aprobar', [BeneficioController::class, 'aprobarParcialidad']);
    Route::post('parcialidades/{id}/rechazar', [BeneficioController::class, 'rechazarParcialidad']);
});

Route::prefix('pesocabal')->middleware(['auth:api'])->group(function () {
    Route::get('perfil', [PesoCabalController::class, 'perfil']);
    Route::get('parcialidades/pendientes', [PesoCabalController::class, 'parcialidadesPorVerificar']);
    Route::post('parcialidades/{id}/registrar-peso', [PesoCabalController::class, 'registrarPeso']);
    Route::post('parcialidades/{id}/generar-boleta', [PesoCabalController::class, 'generarBoleta']);
    Route::get('boletas', [PesoCabalController::class, 'listarBoletas']);
    Route::get('boletas/{id}', [PesoCabalController::class, 'verBoleta']);
});
