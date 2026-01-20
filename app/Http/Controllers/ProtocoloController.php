<?php

namespace App\Http\Controllers;

use App\Jobs\OpenDoorEmergencyJob;
use App\Models\ProtocolRun;
use App\Models\ProtocolRunItem;
use App\Models\Puerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProtocoloController extends Controller
{
    /**
     * Mostrar vista del protocolo de emergencia
     */
    public function index(Request $request): Response
    {
        try {
            $this->authorize('viewAny', ProtocolRun::class);

            $user = $request->user();

            // Verificar permiso
            if (!$user || !$user->hasPermission('protocol_emergencia_open_all')) {
                abort(403, 'No tienes permiso para acceder a esta sección.');
            }

            // Obtener puertas activas con IP de entrada
            $puertas = Puerta::query()
                ->where('activo', true)
                ->whereNotNull('ip_entrada')
                ->orderBy('nombre')
                ->get();

            // Verificar conexiones en paralelo (con caché de 2 minutos)
            try {
                $puertasConectadas = $this->verificarConexionesPuertas($puertas);
            } catch (\Exception $e) {
                // Si hay error en la verificación, usar todas las puertas (fallback)
                Log::error('Error verificando conexiones de puertas en protocolo: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString(),
                ]);
                $puertasConectadas = $puertas;
            }

            // Filtrar y mapear solo las puertas con conexión
            $puertas = $puertasConectadas
                ->map(function ($puerta) {
                    return [
                        'id' => $puerta->id,
                        'nombre' => $puerta->nombre,
                        'ip_entrada' => $puerta->ip_entrada,
                    ];
                })
                ->values();

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
        } catch (\Exception $e) {
            Log::error('Error en ProtocoloController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-lanzar para que Laravel maneje el error apropiadamente
        }
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

        // Obtener todas las puertas activas con IP de entrada
        $puertas = Puerta::query()
            ->where('activo', true)
            ->whereNotNull('ip_entrada')
            ->get();

        // Verificar conexiones en paralelo (sin caché para activación, queremos estado actual)
        $puertas = $this->verificarConexionesPuertas($puertas, useCache: false);

        if ($puertas->isEmpty()) {
            return back()->withErrors(['error' => 'No hay puertas activas con conexión disponible.']);
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
                // Si la conexión es 'sync', disparar directamente sin batch
                $queueConnection = config('queue.default', 'sync');

                if ($queueConnection === 'sync') {
                    // Para desarrollo: ejecutar jobs directamente
                    foreach ($jobs as $job) {
                        dispatch($job);
                    }

                    $protocolRun->update([
                        'estado' => 'en_proceso',
                        'observaciones' => 'Ejecutado en modo sync (desarrollo)',
                    ]);
                } else {
                    // Para producción: usar batch
                    $batch = Bus::batch($jobs)
                        ->name("Protocolo Emergencia - {$user->name}")
                        ->dispatch();

                    $protocolRun->update([
                        'estado' => 'en_proceso',
                        'observaciones' => "Batch ID: {$batch->id}",
                    ]);
                }
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

    /**
     * Verifica las conexiones de las puertas en paralelo (optimizado)
     *
     * @param \Illuminate\Database\Eloquent\Collection $puertas
     * @param bool $useCache Si usar caché (por defecto true, con TTL de 2 minutos)
     * @return \Illuminate\Database\Eloquent\Collection Puertas con conexión activa
     */
    private function verificarConexionesPuertas($puertas, bool $useCache = true)
    {
        try {
            if ($puertas->isEmpty()) {
                return collect();
            }

            $port = (int) (config('app.door_emergency_port') ?? env('DOOR_EMERGENCY_PORT', 8000));
            $timeoutSeconds = 2.0; // Timeout de 2 segundos por puerta

            // Preparar targets para verificación
            $targets = [];
            $puertasMap = [];

            foreach ($puertas as $puerta) {
                if (!$puerta || !$puerta->ip_entrada) {
                    continue;
                }

                $cacheKey = "protocolo_puerta_conexion_{$puerta->id}";

                // Si usamos caché, verificar primero
                if ($useCache) {
                    try {
                        $cached = Cache::get($cacheKey);
                        if ($cached !== null) {
                            // Si está en caché y es true, incluirla directamente
                            if ($cached === true) {
                                $puertasMap[$puerta->id] = $puerta;
                            }
                            continue;
                        }
                    } catch (\Exception $e) {
                        // Si hay error con caché, continuar sin caché
                        Log::warning('Error accediendo a caché de puerta: ' . $e->getMessage());
                    }
                }

                $targets[] = [
                    'puerta_id' => $puerta->id,
                    'ip' => $puerta->ip_entrada,
                ];
            }

            // Si no hay targets que verificar (todo estaba en caché), retornar
            if (empty($targets)) {
                return collect($puertasMap);
            }

            // Verificar conexiones en paralelo
            $resultados = $this->checkTcpTargets($targets, $port, $timeoutSeconds);

            // Procesar resultados y actualizar caché
            foreach ($resultados as $resultado) {
                if (!isset($resultado['puerta_id'])) {
                    continue;
                }

                $puertaId = $resultado['puerta_id'];
                $conectada = $resultado['ok'] ?? false;

                // Actualizar caché si usamos caché
                if ($useCache) {
                    try {
                        $cacheKey = "protocolo_puerta_conexion_{$puertaId}";
                        Cache::put($cacheKey, $conectada, now()->addMinutes(2));
                    } catch (\Exception $e) {
                        // Si hay error con caché, continuar sin actualizar
                        Log::warning('Error actualizando caché de puerta: ' . $e->getMessage());
                    }
                }

                // Si está conectada, agregarla al mapa
                if ($conectada) {
                    $puerta = $puertas->firstWhere('id', $puertaId);
                    if ($puerta) {
                        $puertasMap[$puertaId] = $puerta;
                    }
                }
            }

            return collect($puertasMap);
        } catch (\Exception $e) {
            Log::error('Error en verificarConexionesPuertas: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            // Retornar colección vacía en caso de error
            return collect();
        }
    }

    /**
     * Verifica conexiones TCP en paralelo (reutilizado de PuertasController)
     *
     * @param array<int, array{puerta_id:int,ip:string}> $targets
     * @param int $port
     * @param float $timeoutSeconds
     * @return array<int, array{puerta_id:int,ip:string,ok:bool}>
     */
    private function checkTcpTargets(array $targets, int $port, float $timeoutSeconds): array
    {
        if (count($targets) === 0) {
            return [];
        }

        // Fallback secuencial si no hay soporte para sockets asíncronos
        if (!function_exists('socket_import_stream')) {
            $out = [];
            foreach ($targets as $t) {
                $ok = false;
                $conexion = @fsockopen($t['ip'], $port, $errno, $errstr, $timeoutSeconds);
                if ($conexion) {
                    fclose($conexion);
                    $ok = true;
                }
                $out[] = [
                    'puerta_id' => $t['puerta_id'],
                    'ip' => $t['ip'],
                    'ok' => $ok,
                ];
            }
            return $out;
        }

        // Verificación en paralelo usando streams
        $streams = [];
        $meta = [];
        $out = [];
        $start = microtime(true);

        // Crear conexiones asíncronas
        foreach ($targets as $t) {
            $errno = 0;
            $errstr = '';
            $uri = "tcp://{$t['ip']}:{$port}";

            $stream = @stream_socket_client(
                $uri,
                $errno,
                $errstr,
                $timeoutSeconds,
                STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT
            );

            if ($stream === false) {
                $out[] = [
                    'puerta_id' => $t['puerta_id'],
                    'ip' => $t['ip'],
                    'ok' => false,
                ];
                continue;
            }

            stream_set_blocking($stream, false);
            $key = (int) $stream;
            $streams[$key] = $stream;
            $meta[$key] = $t;
        }

        // Loop hasta agotar streams o timeout global
        while (count($streams) > 0) {
            $elapsed = microtime(true) - $start;
            $left = $timeoutSeconds - $elapsed;
            if ($left <= 0) {
                break;
            }

            $sec = (int) floor($left);
            $usec = (int) floor(($left - $sec) * 1_000_000);

            $write = array_values($streams);
            $except = array_values($streams);
            $read = [];

            $n = @stream_select($read, $write, $except, $sec, $usec);
            if ($n === false) {
                break;
            }

            // Streams "writable" => connect success o failure
            foreach ($write as $s) {
                $key = (int) $s;
                $t = $meta[$key] ?? null;
                if (!$t) {
                    @fclose($s);
                    unset($streams[$key], $meta[$key]);
                    continue;
                }

                $ok = false;
                try {
                    $sock = @socket_import_stream($s);
                    if ($sock !== false) {
                        // Verificar que las constantes estén definidas
                        if (defined('SOL_SOCKET') && defined('SO_ERROR')) {
                            $err = @socket_get_option($sock, SOL_SOCKET, SO_ERROR);
                            $ok = ($err === 0);
                        } else {
                            // Fallback: si la conexión se estableció, asumir que está OK
                            $ok = true;
                        }
                        @socket_close($sock);
                    }
                } catch (\Exception $e) {
                    // Si hay error, considerar como no conectada
                    $ok = false;
                }

                $out[] = [
                    'puerta_id' => $t['puerta_id'],
                    'ip' => $t['ip'],
                    'ok' => $ok,
                ];

                @fclose($s);
                unset($streams[$key], $meta[$key]);
            }
        }

        // Cerrar streams restantes (timeout)
        foreach ($streams as $s) {
            @fclose($s);
            $key = (int) $s;
            $t = $meta[$key] ?? null;
            if ($t) {
                $out[] = [
                    'puerta_id' => $t['puerta_id'],
                    'ip' => $t['ip'],
                    'ok' => false,
                ];
            }
        }

        return $out;
    }
}
