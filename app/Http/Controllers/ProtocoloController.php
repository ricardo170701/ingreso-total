<?php

namespace App\Http\Controllers;

use App\Jobs\OpenDoorEmergencyJob;
use App\Models\ProtocolRun;
use App\Models\ProtocolRunItem;
use App\Models\Puerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProtocoloController extends Controller
{
    /**
     * Mostrar vista del protocolo de emergencia
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ProtocolRun::class);

        $user = $request->user();

        // Verificar permiso
        if (!$user || !$user->hasPermission('protocol_emergencia_open_all')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        // Obtener puertas activas con IP de entrada (solo entrada para protocolo de emergencia)
        $puertas = Puerta::query()
            ->where('activo', true)
            ->whereNotNull('ip_entrada')
            ->orderBy('nombre')
            ->get()
            ->map(function ($puerta) {
                return [
                    'id' => $puerta->id,
                    'nombre' => $puerta->nombre,
                    'ip_entrada' => $puerta->ip_entrada,
                ];
            });

        // Últimas corridas de emergencia (últimas 5)
        $ultimasCorridas = ProtocolRun::query()
            ->with('user')
            ->where('tipo', 'emergencia')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($run) {
                return [
                    'id' => $run->id,
                    'usuario' => $run->user->name ?? 'N/A',
                    'estado' => $run->estado,
                    'total_puertas' => $run->total_puertas,
                    'puertas_exitosas' => $run->puertas_exitosas,
                    'puertas_fallidas' => $run->puertas_fallidas,
                    'fecha' => $run->created_at->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('Protocolo/Index', [
            'puertas' => $puertas,
            'ultimasCorridas' => $ultimasCorridas,
        ]);
    }

    /**
     * Activar protocolo de emergencia (abrir todas las puertas)
     */
    public function activateEmergency(Request $request)
    {
        $this->authorize('activateEmergency', ProtocolRun::class);

        $user = $request->user();

        // Verificar permiso
        if (!$user || !$user->hasPermission('protocol_emergencia_open_all')) {
            return back()->withErrors(['error' => 'No tienes permiso para ejecutar el protocolo de emergencia.']);
        }

        $request->validate([
            'duration_seconds' => ['nullable', 'integer', 'min:10', 'max:3600'],
        ]);

        $durationSeconds = $request->input('duration_seconds', 900); // 15 minutos por defecto

        // Obtener todas las puertas activas con IP de entrada (solo entrada para protocolo de emergencia)
        $puertas = Puerta::query()
            ->where('activo', true)
            ->whereNotNull('ip_entrada')
            ->get();

        if ($puertas->isEmpty()) {
            return back()->withErrors(['error' => 'No hay puertas activas con IP de entrada configurada.']);
        }

        DB::beginTransaction();
        try {
            // Crear el protocol run
            $protocolRun = ProtocolRun::create([
                'user_id' => $user->id,
                'tipo' => 'emergencia',
                'duration_seconds' => $durationSeconds,
                'estado' => 'iniciado',
                'total_puertas' => 0,
                'puertas_exitosas' => 0,
                'puertas_fallidas' => 0,
            ]);

            $jobs = [];
            $totalPuertas = 0;

            // Crear items y jobs para cada puerta (solo usando IP de entrada)
            foreach ($puertas as $puerta) {
                $ip = $puerta->ip_entrada;

                if (!$ip) {
                    continue; // Saltar si no tiene IP de entrada
                }

                // Crear el item
                $item = ProtocolRunItem::create([
                    'protocol_run_id' => $protocolRun->id,
                    'puerta_id' => $puerta->id,
                    'ip' => $ip,
                    'tipo_ip' => 'entrada',
                    'estado' => 'pendiente',
                ]);

                // Agregar job a la lista
                $jobs[] = new OpenDoorEmergencyJob($item->id, $ip, $durationSeconds);
                $totalPuertas++;
            }

            // Actualizar total de puertas
            $protocolRun->update(['total_puertas' => $totalPuertas]);

            // Disparar todos los jobs en paralelo usando batch
            if (!empty($jobs)) {
                $batch = Bus::batch($jobs)
                    ->name("Protocolo Emergencia - {$user->name}")
                    ->dispatch();

                $protocolRun->update([
                    'estado' => 'en_proceso',
                    'observaciones' => "Batch ID: {$batch->id}",
                ]);
            } else {
                $protocolRun->update([
                    'estado' => 'fallido',
                    'observaciones' => 'No se encontraron puertas con IPs válidas.',
                ]);
            }

            DB::commit();

            return back()->with('message', "Protocolo de emergencia iniciado. Se están abriendo {$totalPuertas} puerta(s) por {$durationSeconds} segundos.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al iniciar el protocolo: ' . $e->getMessage()]);
        }
    }
}
