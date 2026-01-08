<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Mantenimiento;
use App\Models\Material;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\Role;
use App\Models\TipoPuerta;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    /**
     * Mostrar página de reportes
     */
    public function index(Request $request): Response
    {
        if (!$request->user()->hasPermission('view_reportes')) {
            abort(403, 'No tienes permiso para ver reportes.');
        }

        // Obtener datos para los filtros
        $roles = Role::query()
            ->whereIn('name', ['funcionario', 'visitante'])
            ->orderBy('name')
            ->get(['id', 'name']);
        $cargos = Cargo::query()->orderBy('name')->get(['id', 'name']);
        $departamentos = Departamento::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get(['id', 'nombre']);
        $puertas = Puerta::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $tiposPuerta = TipoPuerta::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $materiales = Material::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $usuarios = User::query()
            ->where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Reportes/Index', [
            'roles' => $roles,
            'cargos' => $cargos,
            'departamentos' => $departamentos,
            'pisos' => $pisos,
            'puertas' => $puertas,
            'tiposPuerta' => $tiposPuerta,
            'materiales' => $materiales,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Exportar reporte de usuarios
     */
    public function exportarUsuarios(Request $request): StreamedResponse
    {
        if (!$request->user()->hasPermission('view_reportes') || !$request->user()->hasPermission('view_users')) {
            abort(403, 'No tienes permiso para exportar reportes de usuarios.');
        }

        $filtros = $request->only([
            'role_id',
            'cargo_id',
            'departamento_id',
            'activo',
        ]);

        $query = User::query()
            ->with(['role', 'cargo', 'departamento']);

        if (!empty($filtros['role_id'])) {
            $query->where('role_id', $filtros['role_id']);
        }

        if (!empty($filtros['cargo_id'])) {
            $query->where('cargo_id', $filtros['cargo_id']);
        }

        if (!empty($filtros['departamento_id'])) {
            $query->where('departamento_id', $filtros['departamento_id']);
        }

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $query->where('activo', (bool) $filtros['activo']);
        }

        $usuarios = $query->orderBy('name')->get();

        $filename = 'reporte_usuarios_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($usuarios) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8 (Excel compatibility)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'ID',
                'Nombre',
                'Email',
                'Rol',
                'Cargo',
                'Departamento',
                'Activo',
                'Fecha Expiración',
            ]);

            // Datos
            foreach ($usuarios as $usuario) {
                fputcsv($file, [
                    $usuario->id,
                    $usuario->name,
                    $usuario->email,
                    $usuario->role?->name ?? '',
                    $usuario->cargo?->name ?? '',
                    $usuario->departamento?->nombre ?? '',
                    $usuario->activo ? 'Sí' : 'No',
                    $usuario->fecha_expiracion?->format('Y-m-d') ?? '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Exportar reporte de accesos
     */
    public function exportarAccesos(Request $request): StreamedResponse
    {
        if (!$request->user()->hasPermission('view_reportes')) {
            abort(403, 'No tienes permiso para exportar reportes.');
        }

        $filtros = $request->only([
            'fecha_desde',
            'fecha_hasta',
            'user_id',
            'puerta_id',
            'permitido',
        ]);

        $query = Acceso::query()
            ->with(['user', 'puerta']);

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_acceso', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_acceso', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['user_id'])) {
            $query->where('user_id', $filtros['user_id']);
        }

        if (!empty($filtros['puerta_id'])) {
            $query->where('puerta_id', $filtros['puerta_id']);
        }

        if (isset($filtros['permitido']) && $filtros['permitido'] !== '') {
            $query->where('permitido', (bool) $filtros['permitido']);
        }

        $accesos = $query->orderByDesc('fecha_acceso')->get();

        $filename = 'reporte_accesos_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($accesos) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'ID',
                'Fecha y Hora',
                'Usuario',
                'Email Usuario',
                'Puerta',
                'Tipo Evento',
                'Permitido',
                'Motivo Denegación',
                'Observaciones',
            ]);

            // Datos
            foreach ($accesos as $acceso) {
                fputcsv($file, [
                    $acceso->id,
                    $acceso->fecha_acceso?->format('Y-m-d H:i:s') ?? '',
                    $acceso->user?->name ?? 'N/A',
                    $acceso->user?->email ?? 'N/A',
                    $acceso->puerta?->nombre ?? 'N/A',
                    $acceso->tipo_evento ?? '',
                    $acceso->permitido ? 'Sí' : 'No',
                    $acceso->motivo_denegacion ?? '',
                    $acceso->observaciones ?? '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Exportar reporte de mantenimientos
     */
    public function exportarMantenimientos(Request $request): StreamedResponse
    {
        if (!$request->user()->hasPermission('view_reportes') || !$request->user()->hasPermission('view_mantenimientos')) {
            abort(403, 'No tienes permiso para exportar reportes de mantenimientos.');
        }

        $filtros = $request->only([
            'fecha_desde',
            'fecha_hasta',
            'puerta_id',
            'tipo',
            'usuario_id',
        ]);

        $query = Mantenimiento::query()
            ->with(['puerta', 'usuario']);

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_mantenimiento', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_mantenimiento', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['puerta_id'])) {
            $query->where('puerta_id', $filtros['puerta_id']);
        }

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        $mantenimientos = $query->orderByDesc('fecha_mantenimiento')->get();

        $filename = 'reporte_mantenimientos_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($mantenimientos) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'ID',
                'Fecha Mantenimiento',
                'Puerta',
                'Usuario',
                'Tipo',
                'Fecha Fin Programada',
                'Otros Defectos',
                'Observaciones',
            ]);

            // Datos
            foreach ($mantenimientos as $mantenimiento) {
                fputcsv($file, [
                    $mantenimiento->id,
                    $mantenimiento->fecha_mantenimiento?->format('Y-m-d') ?? '',
                    $mantenimiento->puerta?->nombre ?? 'N/A',
                    $mantenimiento->usuario?->name ?? 'N/A',
                    $mantenimiento->tipo ?? '',
                    $mantenimiento->fecha_fin_programada?->format('Y-m-d') ?? '',
                    $mantenimiento->otros_defectos ?? '',
                    $mantenimiento->observaciones ?? '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Exportar reporte de puertas
     */
    public function exportarPuertas(Request $request): StreamedResponse
    {
        if (!$request->user()->hasPermission('view_reportes') || !$request->user()->hasPermission('view_puertas')) {
            abort(403, 'No tienes permiso para exportar reportes de puertas.');
        }

        $filtros = $request->only([
            'piso_id',
            'tipo_puerta_id',
            'material_id',
            'activo',
        ]);

        $query = Puerta::query()
            ->with(['piso', 'tipoPuerta', 'material']);

        if (!empty($filtros['piso_id'])) {
            $query->where('piso_id', $filtros['piso_id']);
        }

        if (!empty($filtros['tipo_puerta_id'])) {
            $query->where('tipo_puerta_id', $filtros['tipo_puerta_id']);
        }

        if (!empty($filtros['material_id'])) {
            $query->where('material_id', $filtros['material_id']);
        }

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $query->where('activo', (bool) $filtros['activo']);
        }

        $puertas = $query->orderBy('nombre')->get();

        $filename = 'reporte_puertas_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($puertas) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'ID',
                'Nombre',
                'Piso',
                'Tipo',
                'Material',
                'IP Entrada',
                'IP Salida',
                'Tiempo Apertura (seg)',
                'Alto (cm)',
                'Largo (cm)',
                'Ancho (cm)',
                'Peso (kg)',
                'Activo',
            ]);

            // Datos
            foreach ($puertas as $puerta) {
                fputcsv($file, [
                    $puerta->id,
                    $puerta->nombre ?? '',
                    $puerta->piso?->nombre ?? '',
                    $puerta->tipoPuerta?->nombre ?? '',
                    $puerta->material?->nombre ?? '',
                    $puerta->ip_entrada ?? '',
                    $puerta->ip_salida ?? '',
                    $puerta->tiempo_apertura ?? '',
                    $puerta->alto ?? '',
                    $puerta->largo ?? '',
                    $puerta->ancho ?? '',
                    $puerta->peso ?? '',
                    $puerta->activo ? 'Sí' : 'No',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
