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
    // Nota: Se usan nombres únicos con prefijo 'api.' para evitar conflictos con rutas web
    Route::apiResource('roles', RoleController::class)->names([
        'index' => 'api.roles.index',
        'show' => 'api.roles.show',
        'store' => 'api.roles.store',
        'update' => 'api.roles.update',
        'destroy' => 'api.roles.destroy',
    ]);
    Route::apiResource('cargos', CargoController::class)->names([
        'index' => 'api.cargos.index',
        'show' => 'api.cargos.show',
        'store' => 'api.cargos.store',
        'update' => 'api.cargos.update',
        'destroy' => 'api.cargos.destroy',
    ]);
    Route::apiResource('zonas', ZonaController::class)->names([
        'index' => 'api.zonas.index',
        'show' => 'api.zonas.show',
        'store' => 'api.zonas.store',
        'update' => 'api.zonas.update',
        'destroy' => 'api.zonas.destroy',
    ]);
    Route::apiResource('puertas', PuertaController::class)->names([
        'index' => 'api.puertas.index',
        'show' => 'api.puertas.show',
        'store' => 'api.puertas.store',
        'update' => 'api.puertas.update',
        'destroy' => 'api.puertas.destroy',
    ]);

    // Permisos: cargo -> puertas (pivot cargo_puerta_acceso)
    Route::get('cargos/{cargo}/puertas', [CargoController::class, 'puertas']);
    Route::post('cargos/{cargo}/puertas', [CargoController::class, 'upsertPuerta']);
    Route::delete('cargos/{cargo}/puertas/{puerta}', [CargoController::class, 'revokePuerta']);
});
