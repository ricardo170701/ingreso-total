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
use Illuminate\Database\QueryException;
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

        $puertas = collect();
        $ultimasCorridas = collect();

        try {
            // Obtener puertas activas con IP (entrada o salida) - igual criterio que módulo "Puertas"
            $puertasAll = Puerta::query()
                ->where('activo', true)
                ->where(function ($q) {
                    $q->whereNotNull('ip_entrada')
                        ->orWhereNotNull('ip_salida');
                })
                ->orderBy('nombre')
                ->get();

            // Verificar conexiones y decidir qué IP se usará (entrada si responde; si no, salida)
            $puertasElegibles = $this->verificarPuertasParaEmergencia($puertasAll, true);

            $puertas = $puertasElegibles
                ->map(function (array $row) {
                    /** @var \App\Models\Puerta $puerta */
                    $puerta = $row['puerta'];
                    return [
                        'id' => $puerta->id,
                        'nombre' => $puerta->nombre,
                        'ip_entrada' => $puerta->ip_entrada,
                        'ip_salida' => $puerta->ip_salida,
                        'ip_usada' => $row['ip_usada'],
                        'tipo_ip_usada' => $row['tipo_ip_usada'],
                    ];
                })
                ->values();
        } catch (QueryException | \Throwable $e) {
            Log::error('Protocolo@index: error cargando/verificando puertas: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('message', 'No se pudieron verificar las puertas en este momento. Intenta nuevamente.');
            $puertas = collect();
        }

        try {
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
        } catch (QueryException | \Throwable $e) {
            Log::error('Protocolo@index: error cargando corridas: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('message', 'No se pudo cargar el historial del protocolo. Revisa migraciones/BD.');
            $ultimasCorridas = collect();
        }

        return Inertia::render('Protocolo/Index', [
            'puertas' => $puertas,
            'ultimasCorridas' => $ultimasCorridas,
        ]);
    }

    /**
     * Diagnosticar conexiones de puertas (para debugging)
     */
    public function diagnosticarConexiones(Request $request)
    {
        $this->authorize('viewAny', ProtocolRun::class);

        $puertas = Puerta::query()
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNotNull('ip_entrada')
                    ->orWhereNotNull('ip_salida');
            })
            ->get();

        $port = 8000;
        $timeoutSeconds = 1.5;
        $resultados = [];

        foreach ($puertas as $puerta) {
            $entradaStatus = null;
            $salidaStatus = null;

            if ($puerta->ip_entrada) {
                $start = microtime(true);
                $conexion = @fsockopen($puerta->ip_entrada, $port, $errno, $errstr, $timeoutSeconds);
                $tiempo = round((microtime(true) - $start) * 1000, 2);

                if ($conexion) {
                    fclose($conexion);
                    $entradaStatus = ['ok' => true, 'tiempo_ms' => $tiempo];
                } else {
                    $entradaStatus = ['ok' => false, 'error' => "$errno: $errstr", 'tiempo_ms' => $tiempo];
                }
            }

            if ($puerta->ip_salida) {
                $start = microtime(true);
                $conexion = @fsockopen($puerta->ip_salida, $port, $errno, $errstr, $timeoutSeconds);
                $tiempo = round((microtime(true) - $start) * 1000, 2);

                if ($conexion) {
                    fclose($conexion);
                    $salidaStatus = ['ok' => true, 'tiempo_ms' => $tiempo];
                } else {
                    $salidaStatus = ['ok' => false, 'error' => "$errno: $errstr", 'tiempo_ms' => $tiempo];
                }
            }

            $resultados[] = [
                'id' => $puerta->id,
                'nombre' => $puerta->nombre,
                'ip_entrada' => $puerta->ip_entrada,
                'ip_salida' => $puerta->ip_salida,
                'entrada_status' => $entradaStatus,
                'salida_status' => $salidaStatus,
            ];
        }

        return response()->json([
            'ok' => true,
            'puerto_verificado' => $port,
            'timeout_segundos' => $timeoutSeconds,
            'total_puertas' => $puertas->count(),
            'puertas' => $resultados,
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
            'force' => ['nullable', 'boolean'], // Forzar ejecución sin verificar conexiones
        ]);

        $durationSeconds = $request->input('duration_seconds', 900); // 15 minutos por defecto
        $force = $request->boolean('force', false);

        // Obtener todas las puertas activas con IP (entrada o salida)
        $puertasAll = Puerta::query()
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNotNull('ip_entrada')
                    ->orWhereNotNull('ip_salida');
            })
            ->get();

        Log::info('Protocolo emergencia: verificando conexiones', [
            'total_puertas' => $puertasAll->count(),
            'puertas' => $puertasAll->map(fn($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'ip_entrada' => $p->ip_entrada,
                'ip_salida' => $p->ip_salida,
            ])->toArray(),
        ]);

        // Sin caché: queremos estado actual (a menos que se fuerce)
        if ($force) {
            // Modo forzado: usar todas las puertas sin verificar
            Log::warning('Protocolo emergencia: FORZADO - sin verificación de conexiones');
            $puertasElegibles = $puertasAll->map(function ($puerta) {
                // Preferir IP entrada si existe, sino salida
                $ipUsada = $puerta->ip_entrada ?? $puerta->ip_salida;
                $tipoIp = $puerta->ip_entrada ? 'entrada' : 'salida';

                return [
                    'puerta' => $puerta,
                    'ip_usada' => $ipUsada,
                    'tipo_ip_usada' => $tipoIp,
                    'entrada_ok' => null,
                    'salida_ok' => null,
                ];
            });
        } else {
            // Modo normal: verificar conexiones
            $puertasElegibles = $this->verificarPuertasParaEmergencia($puertasAll, false);

            Log::info('Protocolo emergencia: resultado verificación', [
                'puertas_elegibles' => $puertasElegibles->count(),
                'detalles' => $puertasElegibles->map(fn($p) => [
                    'puerta_id' => $p['puerta']->id,
                    'nombre' => $p['puerta']->nombre,
                    'ip_usada' => $p['ip_usada'],
                    'tipo_ip_usada' => $p['tipo_ip_usada'],
                ])->toArray(),
            ]);

            if ($puertasElegibles->isEmpty()) {
                return back()->withErrors([
                    'error' => 'No hay puertas con servidor de emergencia activo (puerto 8000). Verifica que ingreso.py esté corriendo en las Raspberries. Usa el botón "Diagnosticar Conexiones" para más detalles.'
                ]);
            }
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

            // Crear items y jobs para cada puerta elegible (entrada si está conectada; si no, salida)
            foreach ($puertasElegibles as $row) {
                /** @var \App\Models\Puerta $puerta */
                $puerta = $row['puerta'];
                $ip = $row['ip_usada'];
                $tipoIp = $row['tipo_ip_usada']; // entrada|salida

                // Crear el item
                $item = ProtocolRunItem::create([
                    'protocol_run_id' => $protocolRun->id,
                    'puerta_id' => $puerta->id,
                    'ip' => $ip,
                    'tipo_ip' => $tipoIp,
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
     * Determina qué puertas se incluirán en el protocolo (mismo criterio visual que "Puertas").
     *
     * Una puerta es elegible si responde al puerto 8000 en al menos una IP (entrada o salida).
     * Preferencia para ejecutar: entrada si responde; si no, salida.
     *
     * @param \Illuminate\Database\Eloquent\Collection $puertas
     * @param bool $useCache true para UI (rápido), false para ejecutar (estado actual)
     * @return \Illuminate\Support\Collection<int, array{puerta:\App\Models\Puerta,ip_usada:string,tipo_ip_usada:string,entrada_ok:bool|null,salida_ok:bool|null}>
     */
    private function verificarPuertasParaEmergencia($puertas, bool $useCache = true)
    {
        if (!$puertas || $puertas->isEmpty()) {
            return collect();
        }

        // Puerto donde corre el servidor HTTP de emergencia (ingreso.py)
        $port = 8000;
        // Timeout aumentado para redes lentas o Raspberries cargadas
        $timeoutSeconds = 1.5;

        $targets = [];
        foreach ($puertas as $puerta) {
            if (!$puerta) {
                continue;
            }
            if ($puerta->ip_entrada) {
                $targets[] = ['puerta_id' => (int) $puerta->id, 'lado' => 'entrada', 'ip' => (string) $puerta->ip_entrada];
            }
            if ($puerta->ip_salida) {
                $targets[] = ['puerta_id' => (int) $puerta->id, 'lado' => 'salida', 'ip' => (string) $puerta->ip_salida];
            }
        }

        if (count($targets) === 0) {
            return collect();
        }

        // Estados por puerta/lado. Si useCache, primero intenta caché de "Puertas".
        $estados = [];
        if ($useCache) {
            foreach ($targets as $t) {
                $cacheKey = "puerta_conexion_{$t['lado']}_{$t['puerta_id']}";
                $cached = Cache::get($cacheKey);
                if (is_bool($cached)) {
                    $estados[(int) $t['puerta_id']][(string) $t['lado']] = $cached;
                }
            }
        }

        // Verificar solo faltantes (o todo si useCache=false)
        $faltantes = [];
        foreach ($targets as $t) {
            $pid = (int) $t['puerta_id'];
            $lado = (string) $t['lado'];
            if (!$useCache || !isset($estados[$pid][$lado])) {
                $faltantes[] = $t;
            }
        }

        if (count($faltantes) > 0) {
            $resultados = $this->checkTcpTargetsLados($faltantes, $port, $timeoutSeconds);

            // Log detallado de resultados
            Log::debug('Verificación TCP puerto 8000', [
                'port' => $port,
                'timeout' => $timeoutSeconds,
                'resultados' => array_map(fn($r) => [
                    'puerta_id' => $r['puerta_id'],
                    'lado' => $r['lado'],
                    'ip' => $r['ip'],
                    'ok' => $r['ok'] ?? false,
                ], $resultados),
            ]);

            foreach ($resultados as $r) {
                $pid = (int) $r['puerta_id'];
                $lado = (string) $r['lado'];
                $ok = (bool) ($r['ok'] ?? false);
                $estados[$pid][$lado] = $ok;

                if ($useCache) {
                    Cache::put("puerta_conexion_{$lado}_{$pid}", $ok, now()->addMinutes(15));
                }
            }
        }

        $out = collect();
        foreach ($puertas as $puerta) {
            $pid = (int) $puerta->id;
            $entradaOk = $estados[$pid]['entrada'] ?? null;
            $salidaOk = $estados[$pid]['salida'] ?? null;

            if ($entradaOk === true && $puerta->ip_entrada) {
                $out->push([
                    'puerta' => $puerta,
                    'ip_usada' => (string) $puerta->ip_entrada,
                    'tipo_ip_usada' => 'entrada',
                    'entrada_ok' => $entradaOk,
                    'salida_ok' => $salidaOk,
                ]);
            } elseif ($salidaOk === true && $puerta->ip_salida) {
                $out->push([
                    'puerta' => $puerta,
                    'ip_usada' => (string) $puerta->ip_salida,
                    'tipo_ip_usada' => 'salida',
                    'entrada_ok' => $entradaOk,
                    'salida_ok' => $salidaOk,
                ]);
            }
        }

        return $out;
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

    /**
     * Igual que checkTcpTargets, pero preserva el "lado" (entrada/salida).
     *
     * @param array<int, array{puerta_id:int,lado:string,ip:string}> $targets
     * @return array<int, array{puerta_id:int,lado:string,ip:string,ok:bool}>
     */
    private function checkTcpTargetsLados(array $targets, int $port, float $timeoutSeconds): array
    {
        if (count($targets) === 0) {
            return [];
        }

        // Fallback secuencial
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
                    'puerta_id' => (int) $t['puerta_id'],
                    'lado' => (string) $t['lado'],
                    'ip' => (string) $t['ip'],
                    'ok' => $ok,
                ];
            }
            return $out;
        }

        $streams = [];
        $meta = [];
        $out = [];
        $start = microtime(true);

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
                    'puerta_id' => (int) $t['puerta_id'],
                    'lado' => (string) $t['lado'],
                    'ip' => (string) $t['ip'],
                    'ok' => false,
                ];
                continue;
            }

            stream_set_blocking($stream, false);
            $key = (int) $stream;
            $streams[$key] = $stream;
            $meta[$key] = $t;
        }

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
                        if (defined('SOL_SOCKET') && defined('SO_ERROR')) {
                            $err = @socket_get_option($sock, SOL_SOCKET, SO_ERROR);
                            $ok = ($err === 0);
                        } else {
                            $ok = true;
                        }
                        @socket_close($sock);
                    }
                } catch (\Exception $e) {
                    $ok = false;
                }

                $out[] = [
                    'puerta_id' => (int) $t['puerta_id'],
                    'lado' => (string) $t['lado'],
                    'ip' => (string) $t['ip'],
                    'ok' => $ok,
                ];

                @fclose($s);
                unset($streams[$key], $meta[$key]);
            }
        }

        // Timeouts pendientes
        foreach ($streams as $s) {
            @fclose($s);
            $key = (int) $s;
            $t = $meta[$key] ?? null;
            if ($t) {
                $out[] = [
                    'puerta_id' => (int) $t['puerta_id'],
                    'lado' => (string) $t['lado'],
                    'ip' => (string) $t['ip'],
                    'ok' => false,
                ];
            }
        }

        return $out;
    }
}
