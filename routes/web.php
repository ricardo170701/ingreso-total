<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrToolController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PuertasController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\CargosController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MantenimientosController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\DepartamentosController;
use App\Http\Controllers\ReportesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Usuarios (CRUD web)
    Route::get('/usuarios', [UsersController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsersController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsersController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{user}/editar', [UsersController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{user}', [UsersController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{user}', [UsersController::class, 'destroy'])->name('usuarios.destroy');

    // Puertas (CRUD web)
    Route::resource('puertas', PuertasController::class);

    // Cargos y Permisos (CRUD web)
    Route::resource('cargos', CargosController::class);
    Route::post('/cargos/{cargo}/puertas', [CargosController::class, 'upsertPuerta'])->name('cargos.puertas.store');
    Route::delete('/cargos/{cargo}/puertas/{puerta}', [CargosController::class, 'revokePuerta'])->name('cargos.puertas.destroy');
    Route::put('/cargos/{cargo}/permissions', [CargosController::class, 'updatePermissions'])->name('cargos.permissions.update');

    // Roles y Permisos del Sistema
    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::put('/roles/{role}/permissions', [RolesController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Ingreso - Generar QR
    Route::get('/ingreso', [IngresoController::class, 'index'])->name('ingreso.index');
    Route::post('/ingreso', [IngresoController::class, 'generate'])->name('ingreso.generate');
    Route::post('/ingreso/{qr}/enviar-correo', [IngresoController::class, 'sendEmail'])->name('ingreso.send-email');
    Route::get('/ingreso/{qr}/descargar', [IngresoController::class, 'download'])->name('ingreso.download')->middleware('auth');

    // Módulo básico: generar un QR con un "código" (texto) para pruebas rápidas.
    Route::get('/qr', [QrToolController::class, 'index'])->name('qr.tool');
    Route::post('/qr', [QrToolController::class, 'generate'])->name('qr.tool.generate');

    // Mantenimientos
    Route::resource('mantenimientos', MantenimientosController::class);
    Route::delete('/mantenimientos/imagenes/{imagen}', [MantenimientosController::class, 'eliminarImagen'])->name('mantenimientos.imagenes.destroy');

    // Soporte
    Route::get('/soporte', [SoporteController::class, 'index'])->name('soporte.index');

    // Departamentos
    Route::resource('departamentos', DepartamentosController::class);

    // Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar/usuarios', [ReportesController::class, 'exportarUsuarios'])->name('reportes.exportar.usuarios');
    Route::get('/reportes/exportar/accesos', [ReportesController::class, 'exportarAccesos'])->name('reportes.exportar.accesos');
    Route::get('/reportes/exportar/mantenimientos', [ReportesController::class, 'exportarMantenimientos'])->name('reportes.exportar.mantenimientos');
    Route::get('/reportes/exportar/puertas', [ReportesController::class, 'exportarPuertas'])->name('reportes.exportar.puertas');
});
