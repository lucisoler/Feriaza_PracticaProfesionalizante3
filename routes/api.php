<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\Coordinador\FerianteController as CoordinadorFerianteController;
use App\Http\Controllers\Api\MiCuentaController;
use Illuminate\Support\Facades\Route;

/*
| API REST de Feriaza (docs/diseno-api.md).
| Laravel agrega el prefijo /api; acá se suma /v1.
*/
Route::prefix('v1')->group(function () {

    // === Públicas ===
    Route::get('/categorias', [CategoriaController::class, 'index']);

    // Máximo 6 intentos por minuto para frenar ataques de fuerza bruta
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/feriantes/registro', [AuthController::class, 'registro']);
        Route::post('/feriantes/login', [AuthController::class, 'loginFeriante']);
        Route::post('/coordinador/login', [AuthController::class, 'loginCoordinador']);
    });

    // === Cualquier usuario con token (feriante o coordinador) ===
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // === Feriante: token con habilidad 'feriante' ===
    Route::middleware(['auth:sanctum', 'abilities:feriante'])->group(function () {
        Route::get('/mi-cuenta', [MiCuentaController::class, 'show']);
        Route::patch('/mi-cuenta', [MiCuentaController::class, 'update']);
        Route::delete('/mi-cuenta', [MiCuentaController::class, 'destroy']);
    });

    // === Coordinador: token con habilidad 'coordinador' ===
    Route::middleware(['auth:sanctum', 'abilities:coordinador'])
        ->prefix('coordinador')
        ->group(function () {
            Route::get('/feriantes', [CoordinadorFerianteController::class, 'index']);
            Route::post('/feriantes', [CoordinadorFerianteController::class, 'store']);
            Route::get('/feriantes/{feriante}', [CoordinadorFerianteController::class, 'show']);
            Route::patch('/feriantes/{feriante}', [CoordinadorFerianteController::class, 'update']);
            Route::delete('/feriantes/{feriante}', [CoordinadorFerianteController::class, 'destroy']);
            Route::get('/feriantes/{feriante}/historial', [CoordinadorFerianteController::class, 'historial']);
        });
});
