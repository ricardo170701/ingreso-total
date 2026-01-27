<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Mapeo de rutas a permisos requeridos
     * Si una ruta no está en este mapa, no requiere validación de permiso específico
     */
    private array $routePermissions = [
        // Dashboard
        'dashboard' => 'view_dashboard',
        'dashboard.index' => 'view_dashboard',

        // Usuarios
        'usuarios.index' => 'view_users',
        'usuarios.show' => 'view_users',
        'usuarios.create' => 'create_users',
        'usuarios.store' => 'create_users',
        'usuarios.edit' => 'edit_users',
        'usuarios.update' => 'edit_users',
        'usuarios.destroy' => 'delete_users',

        // Puertas
        'puertas.index' => 'view_puertas',
        'puertas.show' => 'view_puertas',
        'puertas.create' => 'create_puertas',
        'puertas.store' => 'create_puertas',
        'puertas.edit' => 'edit_puertas',
        'puertas.update' => 'edit_puertas',
        'puertas.destroy' => 'delete_puertas',
        'puertas.toggle' => 'toggle_puertas',
        'puertas.reiniciar' => 'reboot_puertas',

        // Cargos
        'cargos.index' => 'view_cargos',
        'cargos.show' => 'view_cargos',
        'cargos.create' => 'create_cargos',
        'cargos.store' => 'create_cargos',
        'cargos.edit' => 'edit_cargos',
        'cargos.update' => 'edit_cargos',
        'cargos.destroy' => 'delete_cargos',
        'cargos.permissions.update' => 'edit_cargos',
        'cargos.pisos.store' => 'edit_cargos',
        'cargos.pisos.destroy' => 'edit_cargos',

        // Roles
        'roles.index' => 'view_roles',
        'roles.permissions.update' => 'edit_roles',

        // Ingreso
        'ingreso.index' => 'view_ingreso',
        'ingreso.generate' => 'create_ingreso',
        'ingreso.tarjetas-nfc.asignar' => 'asignar_tarjetas_nfc',
        'ingreso.tarjetas-nfc.desasignar' => 'asignar_tarjetas_nfc',

        // Tarjetas NFC
        'tarjetas-nfc.index' => 'view_tarjetas_nfc',
        'tarjetas-nfc.show' => 'view_tarjetas_nfc',
        'tarjetas-nfc.create' => 'create_tarjetas_nfc',
        'tarjetas-nfc.store' => 'create_tarjetas_nfc',
        'tarjetas-nfc.edit' => 'edit_tarjetas_nfc',
        'tarjetas-nfc.update' => 'edit_tarjetas_nfc',
        'tarjetas-nfc.desasignar' => 'edit_tarjetas_nfc',
        'tarjetas-nfc.destroy' => 'delete_tarjetas_nfc',

        // UPS
        'ups.index' => 'view_ups',
        'ups.show' => 'view_ups',
        'ups.create' => 'create_ups',
        'ups.store' => 'create_ups',
        'ups.edit' => 'edit_ups',
        'ups.update' => 'edit_ups',
        'ups.destroy' => 'delete_ups',

        // Departamentos
        'departamentos.index' => 'view_departamentos',
        'departamentos.show' => 'view_departamentos',
        'departamentos.create' => 'create_departamentos',
        'departamentos.store' => 'create_departamentos',
        'departamentos.edit' => 'edit_departamentos',
        'departamentos.update' => 'edit_departamentos',
        'departamentos.destroy' => 'delete_departamentos',

        // Reportes
        'reportes.index' => 'view_reportes',
        'reportes.accesos' => 'view_reportes',
        'reportes.exportar.usuarios' => 'view_reportes',
        'reportes.exportar.accesos' => 'view_reportes',
        'reportes.exportar.mantenimientos' => 'view_reportes',
        'reportes.exportar.puertas' => 'view_reportes',

        // Protocolo
        'protocolo.index' => 'view_protocolo',
        'protocolo.emergencia.activate' => 'protocol_emergencia_open_all',

        // Soporte
        'soporte.index' => 'view_soporte',

        // Mantenimientos
        'mantenimientos.index' => 'view_mantenimientos',
        'mantenimientos.show' => 'view_mantenimientos',
        'mantenimientos.create' => 'create_mantenimientos',
        'mantenimientos.store' => 'create_mantenimientos',
        'mantenimientos.edit' => 'edit_mantenimientos',
        'mantenimientos.update' => 'edit_mantenimientos',
        'mantenimientos.destroy' => 'delete_mantenimientos',
        'mantenimientos.completar' => 'edit_mantenimientos',
    ];

    /**
     * Handle an incoming request.
     * 
     * Valida que el usuario autenticado tenga los permisos necesarios
     * para acceder a la ruta solicitada.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, dejar que el middleware 'auth' lo maneje
        if (!$user) {
            return $next($request);
        }

        // Recargar relaciones para asegurar datos actualizados en cada request
        $user->loadMissing(['role', 'cargo.permissions']);

        // Obtener el nombre de la ruta actual
        $routeName = $request->route()?->getName();

        // Si la ruta no tiene nombre o no está en el mapa, permitir acceso
        // (otras rutas pueden tener su propia validación)
        if (!$routeName || !isset($this->routePermissions[$routeName])) {
            return $next($request);
        }

        // Obtener el permiso requerido para esta ruta
        $requiredPermission = $this->routePermissions[$routeName];

        // Permitir acceso a visitantes a ingreso.index sin verificar permiso
        // El middleware RestrictVisitanteWeb ya controla que los visitantes solo puedan acceder a rutas permitidas
        if (($user->role?->name ?? null) === 'visitante' && $routeName === 'ingreso.index') {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso
        if (!$user->hasPermission($requiredPermission)) {
            // Siempre redirigir, incluso para peticiones Inertia/AJAX
            // Inertia maneja las redirecciones correctamente
            // Si es visitante, redirigir a ingreso
            if (($user->role?->name ?? null) === 'visitante') {
                return redirect()
                    ->route('ingreso.index')
                    ->withErrors(['error' => 'No tienes permiso para acceder a esta sección.']);
            }

            // Si intenta acceder al dashboard sin permiso, redirigir al perfil
            if ($routeName === 'dashboard' || $routeName === 'dashboard.index') {
                return redirect()
                    ->route('profile.show')
                    ->withErrors(['error' => 'No tienes permiso para acceder al dashboard.']);
            }

            // Para otras rutas, intentar redirigir al dashboard si tiene permiso
            // Si no tiene permiso de dashboard, redirigir al perfil
            if ($user->hasPermission('view_dashboard')) {
                return redirect()
                    ->route('dashboard')
                    ->withErrors(['error' => 'No tienes permiso para acceder a esta sección.']);
            }

            // Si no tiene permiso de dashboard, redirigir al perfil
            return redirect()
                ->route('profile.show')
                ->withErrors(['error' => 'No tienes permiso para acceder a esta sección.']);
        }

        return $next($request);
    }
}

