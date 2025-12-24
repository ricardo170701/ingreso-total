<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\CodigoQr;
use App\Models\Puerta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        // Estadísticas principales
        $totalUsuarios = User::query()->where('activo', true)->count();
        $totalPuertas = Puerta::query()->where('activo', true)->count();

        // Accesos de hoy
        $accesosHoy = Acceso::query()
            ->whereBetween('fecha_acceso', [$startOfDay, $endOfDay])
            ->count();

        $accesosPermitidosHoy = Acceso::query()
            ->whereBetween('fecha_acceso', [$startOfDay, $endOfDay])
            ->where('permitido', true)
            ->count();

        $accesosDenegadosHoy = Acceso::query()
            ->whereBetween('fecha_acceso', [$startOfDay, $endOfDay])
            ->where('permitido', false)
            ->count();

        // QR activos (no expirados y activos)
        $qrActivos = CodigoQr::query()
            ->where('activo', true)
            ->where('fecha_expiracion', '>', $now)
            ->count();

        // QR generados hoy
        $qrGeneradosHoy = CodigoQr::query()
            ->whereBetween('fecha_generacion', [$startOfDay, $endOfDay])
            ->count();

        // Accesos recientes (últimos 10)
        $accesosRecientes = Acceso::query()
            ->with(['user', 'puerta'])
            ->orderBy('fecha_acceso', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($acceso) {
                return [
                    'id' => $acceso->id,
                    'usuario' => $acceso->user->name ?? 'N/A',
                    'puerta' => $acceso->puerta->nombre ?? 'N/A',
                    'tipo_evento' => $acceso->tipo_evento,
                    'permitido' => $acceso->permitido,
                    'fecha_acceso' => $acceso->fecha_acceso->format('d/m/Y H:i'),
                    'motivo_denegacion' => $acceso->motivo_denegacion,
                ];
            });

        // Estadísticas por puerta (top 5 más usadas hoy)
        $puertasMasUsadas = Acceso::query()
            ->selectRaw('puerta_id, COUNT(*) as total')
            ->whereBetween('fecha_acceso', [$startOfDay, $endOfDay])
            ->where('permitido', true)
            ->groupBy('puerta_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->with('puerta')
            ->get()
            ->map(function ($item) {
                return [
                    'puerta' => $item->puerta->nombre ?? 'N/A',
                    'total' => $item->total,
                ];
            });

        // Accesos por hora (últimas 24 horas)
        $accesosPorHora = Acceso::query()
            ->selectRaw('HOUR(fecha_acceso) as hora, COUNT(*) as total')
            ->where('fecha_acceso', '>=', $now->copy()->subHours(24))
            ->where('permitido', true)
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->map(function ($item) {
                return [
                    'hora' => $item->hora . ':00',
                    'total' => $item->total,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_usuarios' => $totalUsuarios,
                'total_puertas' => $totalPuertas,
                'accesos_hoy' => $accesosHoy,
                'accesos_permitidos_hoy' => $accesosPermitidosHoy,
                'accesos_denegados_hoy' => $accesosDenegadosHoy,
                'qr_activos' => $qrActivos,
                'qr_generados_hoy' => $qrGeneradosHoy,
            ],
            'accesos_recientes' => $accesosRecientes,
            'puertas_mas_usadas' => $puertasMasUsadas,
            'accesos_por_hora' => $accesosPorHora,
        ]);
    }
}
