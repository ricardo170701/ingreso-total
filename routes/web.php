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
use App\Http\Controllers\DependenciasController;
use App\Http\Controllers\SecretariasController;
use App\Http\Controllers\GerenciasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ProtocoloController;
use App\Http\Controllers\UpsController;
use App\Http\Controllers\UpsMantenimientosController;
use App\Http\Controllers\UpsVitacoraController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TarjetasNfcController;

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

    // Recuperación de contraseña
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Cambio de contraseña obligatorio (debe estar antes del middleware force.password.change)
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword']);
});

Route::middleware(['auth', 'force.password.change', 'visitante.restrict', 'permission.check'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Perfil de usuario
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::match(['put', 'post'], '/perfil', [ProfileController::class, 'update'])->name('profile.update');

    // Usuarios (CRUD web)
    Route::get('/usuarios', [UsersController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsersController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsersController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{user}', [UsersController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{user}/editar', [UsersController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{user}', [UsersController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{user}', [UsersController::class, 'destroy'])->name('usuarios.destroy');
    Route::delete('/usuarios/{user}/documentos/{documento}', [UsersController::class, 'destroyDocumento'])->name('usuarios.documentos.destroy');

    // Puertas (CRUD web)
    Route::resource('puertas', PuertasController::class);
    Route::get('/api/puertas/estados-conexion', [PuertasController::class, 'obtenerEstadosConexion'])->name('puertas.estados-conexion');
    Route::post('/api/puertas/refrescar-conexiones', [PuertasController::class, 'refrescarConexiones'])->name('puertas.refrescar-conexiones');
    Route::post('/api/puertas/{puerta}/reiniciar', [PuertasController::class, 'reiniciarPuerta'])->name('puertas.reiniciar');
    Route::post('/api/puertas/{puerta}/toggle', [PuertasController::class, 'togglePuerta'])->name('puertas.toggle');
    Route::get('/api/puertas/{puerta}/estado', [PuertasController::class, 'estadoPuerta'])->name('puertas.estado');

    // Cargos y Permisos (CRUD web)
    Route::resource('cargos', CargosController::class);
    Route::post('/cargos/{cargo}/pisos', [CargosController::class, 'upsertPiso'])->name('cargos.pisos.store');
    Route::delete('/cargos/{cargo}/pisos/{piso}', [CargosController::class, 'revokePiso'])->name('cargos.pisos.destroy');
    Route::post('/cargos/{cargo}/puertas', [CargosController::class, 'upsertPuertas'])->name('cargos.puertas.store');
    Route::put('/cargos/{cargo}/puertas', [CargosController::class, 'syncPuertas'])->name('cargos.puertas.sync');
    Route::delete('/cargos/{cargo}/puertas/{puerta}', [CargosController::class, 'revokePuerta'])->name('cargos.puertas.destroy');
    Route::put('/cargos/{cargo}/permissions', [CargosController::class, 'updatePermissions'])->name('cargos.permissions.update');

    // Roles y Permisos del Sistema
    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::put('/roles/{role}/permissions', [RolesController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Ingreso - Generar QR
    Route::get('/ingreso', [IngresoController::class, 'index'])->name('ingreso.index');
    Route::post('/ingreso', [IngresoController::class, 'generate'])->name('ingreso.generate');
    Route::post('/ingreso/visitantes', [IngresoController::class, 'storeVisitante'])->name('ingreso.visitantes.store');
    Route::post('/ingreso/tarjetas-nfc/asignar', [IngresoController::class, 'asignarTarjetaNfc'])->name('ingreso.tarjetas-nfc.asignar');
    Route::post('/ingreso/tarjetas-nfc/desasignar', [IngresoController::class, 'desasignarTarjetaNfc'])->name('ingreso.tarjetas-nfc.desasignar');
    Route::get('/ingreso/tarjetas-nfc/asignadas', [IngresoController::class, 'obtenerTarjetasAsignadas'])->name('ingreso.tarjetas-nfc.asignadas');
    Route::post('/ingreso/{qr}/enviar-correo', [IngresoController::class, 'sendEmail'])->name('ingreso.send-email');

    // Tarjetas NFC
    Route::resource('tarjetas-nfc', TarjetasNfcController::class)->parameters(['tarjetas-nfc' => 'tarjetaNfc']);
    Route::post('tarjetas-nfc/{tarjetaNfc}/desasignar', [TarjetasNfcController::class, 'desasignar'])->name('tarjetas-nfc.desasignar');

    // Módulo básico: generar un QR con un "código" (texto) para pruebas rápidas.
    Route::get('/qr', [QrToolController::class, 'index'])->name('qr.tool');
    Route::post('/qr', [QrToolController::class, 'generate'])->name('qr.tool.generate');

    // Mantenimientos
    Route::resource('mantenimientos', MantenimientosController::class);
    Route::post('/mantenimientos/{mantenimiento}/completar', [MantenimientosController::class, 'marcarCompletado'])->name('mantenimientos.completar');
    Route::get('/mantenimientos/{mantenimiento}/pdf', [MantenimientosController::class, 'downloadPdf'])->name('mantenimientos.pdf');

    // UPS
    // Importante: evitar que Laravel singularice "ups" como "{up}" (rompe Ziggy/route() en Vue).
    Route::resource('ups', UpsController::class)->parameters(['ups' => 'ups']);
    Route::post('/ups/{ups}/mantenimientos', [UpsMantenimientosController::class, 'store'])->name('ups.mantenimientos.store');
    Route::get('/ups/{ups}/mantenimientos/{mantenimiento}/editar', [UpsMantenimientosController::class, 'edit'])->name('ups.mantenimientos.edit');
    Route::put('/ups/{ups}/mantenimientos/{mantenimiento}', [UpsMantenimientosController::class, 'update'])->name('ups.mantenimientos.update');
    Route::delete('/ups/{ups}/mantenimientos/{mantenimiento}', [UpsMantenimientosController::class, 'destroy'])->name('ups.mantenimientos.destroy');
    Route::post('/ups/{ups}/mantenimientos/{mantenimiento}/completar', [UpsMantenimientosController::class, 'marcarCompletado'])->name('ups.mantenimientos.completar');
    Route::get('/ups/{ups}/mantenimientos/{mantenimiento}/zip', [UpsMantenimientosController::class, 'downloadZip'])->name('ups.mantenimientos.zip');

    // Bitácora de UPS
    Route::get('/ups/{ups}/vitacora', [UpsVitacoraController::class, 'index'])->name('ups.vitacora.index');
    Route::get('/ups/{ups}/vitacora/crear', [UpsVitacoraController::class, 'create'])->name('ups.vitacora.create');
    Route::post('/ups/{ups}/vitacora/analizar', [UpsVitacoraController::class, 'analyzeImage'])->name('ups.vitacora.analyze');
    Route::post('/ups/{ups}/vitacora', [UpsVitacoraController::class, 'store'])->name('ups.vitacora.store');
    Route::get('/ups/{ups}/vitacora/exportar', [UpsVitacoraController::class, 'export'])->name('ups.vitacora.export');
    Route::delete('/ups/{ups}/vitacora/{vitacora}', [UpsVitacoraController::class, 'destroy'])->name('ups.vitacora.destroy');

    // Soporte
    Route::get('/soporte', [SoporteController::class, 'index'])->name('soporte.index');

    // Dependencias (nombre del módulo, pero gestiona Secretarías como recurso principal)
    Route::get('/dependencias', [DependenciasController::class, 'index'])->name('dependencias.index');

    // Secretarías (recurso principal)
    Route::resource('secretarias', SecretariasController::class);

    // Gerencias (nivel secundario: anidadas dentro de Secretarías)
    Route::prefix('secretarias/{secretaria}')->group(function () {
        Route::get('gerencias', [GerenciasController::class, 'index'])->name('gerencias.index');
        Route::get('gerencias/crear', [GerenciasController::class, 'create'])->name('gerencias.create');
        Route::post('gerencias', [GerenciasController::class, 'store'])->name('gerencias.store');

        Route::prefix('gerencias/{gerencia}')->group(function () {
            Route::get('/', [GerenciasController::class, 'show'])->name('gerencias.show');
            Route::get('/editar', [GerenciasController::class, 'edit'])->name('gerencias.edit');
            Route::put('/', [GerenciasController::class, 'update'])->name('gerencias.update');
            Route::delete('/', [GerenciasController::class, 'destroy'])->name('gerencias.destroy');
        });
    });

    // Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/accesos', [ReportesController::class, 'accesos'])->name('reportes.accesos');
    Route::get('/reportes/exportar/usuarios', [ReportesController::class, 'exportarUsuarios'])->name('reportes.exportar.usuarios');
    Route::get('/reportes/exportar/accesos', [ReportesController::class, 'exportarAccesos'])->name('reportes.exportar.accesos');
    Route::get('/reportes/exportar/mantenimientos', [ReportesController::class, 'exportarMantenimientos'])->name('reportes.exportar.mantenimientos');
    Route::get('/reportes/exportar/puertas', [ReportesController::class, 'exportarPuertas'])->name('reportes.exportar.puertas');

    // Protocolo de Emergencia
    Route::get('/protocolo', [ProtocoloController::class, 'index'])->name('protocolo.index');
    Route::post('/protocolo/emergencia', [ProtocoloController::class, 'activateEmergency'])->name('protocolo.emergencia.activate');
    Route::get('/protocolo/diagnosticar', [ProtocoloController::class, 'diagnosticarConexiones'])->name('protocolo.diagnosticar');
});
