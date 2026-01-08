<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictVisitanteWeb
{
    /**
     * Si el usuario tiene rol "visitante", solo permite acceder a:
     * - Ingreso (ver/descargar QR personal)
     * - Soporte
     * - Logout
     * - Perfil
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Recargar relaciones para asegurar que tenemos datos actualizados
        $user->load(['role', 'cargo.permissions']);

        $roleName = $user->role?->name;

        if ($roleName !== 'visitante') {
            return $next($request);
        }

        $allowedRouteNames = [
            'ingreso.index',
            'ingreso.generate',
            'ingreso.download',
            'ingreso.send-email',
            'soporte.index',
            'logout',
            'profile.show',
            'profile.update',
        ];

        if ($request->routeIs($allowedRouteNames)) {
            return $next($request);
        }

        // Si es una petición XHR/JSON, respondemos 403
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Acceso restringido para visitantes.',
            ], 403);
        }

        return redirect()
            ->route('ingreso.index')
            ->withErrors(['error' => 'Tu acceso está limitado a Ingreso y Soporte.']);
    }
}
