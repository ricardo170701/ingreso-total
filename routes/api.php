<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CargoController;
use App\Http\Controllers\Api\ZonaController;
use App\Http\Controllers\Api\PuertaController;
use App\Http\Controllers\Api\CodigoQrController;
use App\Http\Controllers\Api\AccessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

// Endpoint para el lector (puede protegerse con X-DEVICE-KEY vía env ACCESS_DEVICE_KEY)
Route::post('access/verify', [AccessController::class, 'verify']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('qrs', [CodigoQrController::class, 'store']);

    // Catálogos (maestros)
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('cargos', CargoController::class);
    Route::apiResource('zonas', ZonaController::class);
    Route::apiResource('puertas', PuertaController::class);

    // Permisos: cargo -> puertas (pivot cargo_puerta_acceso)
    Route::get('cargos/{cargo}/puertas', [CargoController::class, 'puertas']);
    Route::post('cargos/{cargo}/puertas', [CargoController::class, 'upsertPuerta']);
    Route::delete('cargos/{cargo}/puertas/{puerta}', [CargoController::class, 'revokePuerta']);
});
