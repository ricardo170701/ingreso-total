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
                    'hora' => str_pad($item->hora, 2, '0', STR_PAD_LEFT) . ':00',
                    'total' => (int) $item->total,
                ];
            });

        // Accesos por día (últimos 7 días)
        $accesosPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = $now->copy()->subDays($i);
            $startOfDay = $fecha->copy()->startOfDay();
            $endOfDay = $fecha->copy()->endOfDay();

            $permitidos = Acceso::query()
                ->whereBetween('fecha_acceso', [$startOfDay, $endOfDay])
                ->where('permitido', true)
                ->count();

            $denegados = Acceso::query()
                ->whereBetween('fecha_acceso', [$startOfDay, $endOfDay])
                ->where('permitido', false)
                ->count();

            $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $accesosPorDia[] = [
                'dia' => $fecha->format('d/m'),
                'dia_nombre' => $diasSemana[$fecha->dayOfWeek],
                'permitidos' => $permitidos,
                'denegados' => $denegados,
                'total' => $permitidos + $denegados,
            ];
        }

        // Mantenimientos por estado
        // - Programados: tipo=programado y fecha_límite >= hoy (fallback: fecha_mantenimiento)
        // - Vencidos: tipo=programado y fecha_límite < hoy (fallback: fecha_mantenimiento)
        $mantenimientosProgramados = \App\Models\Mantenimiento::query()
            ->where('tipo', 'programado')
            ->whereRaw('COALESCE(fecha_fin_programada, fecha_mantenimiento) >= ?', [$now->toDateString()])
            ->count();

        $mantenimientosVencidos = \App\Models\Mantenimiento::query()
            ->where('tipo', 'programado')
            ->whereRaw('COALESCE(fecha_fin_programada, fecha_mantenimiento) < ?', [$now->toDateString()])
            ->count();

        $mantenimientosRealizados = \App\Models\Mantenimiento::query()
            ->where('tipo', 'realizado')
            ->whereBetween('fecha_mantenimiento', [$now->copy()->subDays(30), $now])
            ->count();

        // Puertas (tarjetas del dashboard)
        $queryPuertas = Puerta::query()
            ->where('activo', true)
            ->with(['piso']);

        // Filtrar puertas ocultas: solo mostrarlas si el usuario tiene el permiso
        $user = $request->user();
        if (!$user || !$user->hasPermission('view_puertas_ocultas')) {
            $queryPuertas->where('es_oculta', false);
        }

        $puertasDashboard = $queryPuertas
            ->orderBy('nombre')
            ->limit(9)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'imagen' => $p->imagen,
                    'activo' => (bool) $p->activo,
                    'ip_entrada' => $p->ip_entrada,
                    'ip_salida' => $p->ip_salida,
                    'piso' => $p->piso ? ['id' => $p->piso->id, 'nombre' => $p->piso->nombre] : null,
                    'estado_mantenimiento' => $p->estado_mantenimiento,
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
            'puertas_dashboard' => $puertasDashboard,
            'accesos_por_hora' => $accesosPorHora,
            'accesos_por_dia' => $accesosPorDia,
            'mantenimientos' => [
                'programados' => $mantenimientosProgramados,
                'vencidos' => $mantenimientosVencidos,
                'realizados' => $mantenimientosRealizados,
            ],
        ]);
    }
}
