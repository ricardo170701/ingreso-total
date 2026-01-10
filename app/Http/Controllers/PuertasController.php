<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePuertaRequest;
use App\Http\Requests\UpdatePuertaRequest;
use App\Models\Material;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\TipoPuerta;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PuertasController extends Controller
{
    /**
     * Puerto del servicio en Raspberry/puerta para validar conectividad TCP.
     * (Históricamente el proyecto usa 8000 para health/servicio local)
     */
    private const PUERTA_TCP_PORT = 8000;

    /**
     * Timeout (segundos) para chequeo de conectividad. Mantener bajo para no bloquear navegación.
     * En servidores con 1 worker (ej: `php artisan serve`) un request largo impide atender otros.
     */
    private const PUERTA_TCP_TIMEOUT_SECONDS = 0.35;

    /**
     * Listar puertas
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Puerta::class);

        $perPage = (int) ($request->query('per_page', 12));
        $perPage = max(1, min(100, $perPage));
        $pisoId = $request->query('piso_id');

        $query = Puerta::query()
            ->with(['zona', 'piso', 'tipoPuerta', 'material', 'mantenimientos']);

        // Filtrar por piso si se proporciona
        if ($pisoId) {
            $query->where('piso_id', $pisoId);
        }

        $puertas = $query->orderBy('nombre')->paginate($perPage);

        // Agregar estado de mantenimiento a cada puerta
        // Nota: La verificación de conexión se hace de forma asíncrona desde el frontend
        // para no impactar en el tiempo de carga de la página
        $puertas->getCollection()->transform(function ($puerta) {
            $puerta->estado_mantenimiento = $puerta->estado_mantenimiento;
            return $puerta;
        });

        // Cargar todos los pisos para el filtro
        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->withCount('puertas')
            ->get();

        return Inertia::render('Puertas/Index', [
            'puertas' => $puertas,
            'pisos' => $pisos,
            'pisoSeleccionado' => $pisoId ? (int) $pisoId : null,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $this->authorize('create', Puerta::class);

        $zonas = Zona::query()->where('activa', true)->orderBy('nombre')->get();
        $pisos = Piso::query()->where('activo', true)->orderBy('orden')->get();
        $tiposPuerta = TipoPuerta::query()->where('activo', true)->orderBy('nombre')->get();
        $materiales = Material::query()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Puertas/Create', [
            'zonas' => $zonas,
            'pisos' => $pisos,
            'tiposPuerta' => $tiposPuerta,
            'materiales' => $materiales,
        ]);
    }

    /**
     * Guardar nueva puerta
     */
    public function store(StorePuertaRequest $request)
    {
        $this->authorize('create', Puerta::class);

        $data = $request->validated();

        // Manejar la subida de imagen
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('puertas', 'public');
        }

        // Valor por defecto para tiempo_apertura si no se proporciona
        if (!isset($data['tiempo_apertura'])) {
            $data['tiempo_apertura'] = 5;
        }

        $puerta = Puerta::query()->create($data);

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta creada exitosamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Puerta $puerta): Response
    {
        $this->authorize('update', $puerta);

        $puerta->load(['zona', 'piso', 'tipoPuerta', 'material']);
        $zonas = Zona::query()->where('activa', true)->orderBy('nombre')->get();
        $pisos = Piso::query()->where('activo', true)->orderBy('orden')->get();
        $tiposPuerta = TipoPuerta::query()->where('activo', true)->orderBy('nombre')->get();
        $materiales = Material::query()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Puertas/Edit', [
            'puerta' => $puerta,
            'zonas' => $zonas,
            'pisos' => $pisos,
            'tiposPuerta' => $tiposPuerta,
            'materiales' => $materiales,
        ]);
    }

    /**
     * Mostrar "Hoja de vida" de una puerta
     */
    public function show(Puerta $puerta): Response
    {
        $this->authorize('view', $puerta);

        $puerta->load([
            'zona',
            'piso',
            'tipoPuerta',
            'material',
            'mantenimientos' => function ($q) {
                $q->with(['documentos', 'creadoPor', 'editadoPor'])
                    ->orderBy('fecha_mantenimiento', 'desc');
            },
        ]);

        // Estado mantenimiento (usa accessor)
        $puerta->estado_mantenimiento = $puerta->estado_mantenimiento;

        return Inertia::render('Puertas/Show', [
            'puerta' => $puerta,
        ]);
    }

    /**
     * Actualizar puerta
     */
    public function update(UpdatePuertaRequest $request, Puerta $puerta)
    {
        $this->authorize('update', $puerta);

        $data = $request->validated();

        // Manejar la subida de nueva imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($puerta->imagen && Storage::disk('public')->exists($puerta->imagen)) {
                Storage::disk('public')->delete($puerta->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('puertas', 'public');
        }

        $puerta->fill($data);
        $puerta->save();

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta actualizada exitosamente.');
    }

    /**
     * Eliminar puerta
     */
    public function destroy(Puerta $puerta)
    {
        $this->authorize('delete', $puerta);

        // Eliminar imagen si existe
        if ($puerta->imagen && Storage::disk('public')->exists($puerta->imagen)) {
            Storage::disk('public')->delete($puerta->imagen);
        }

        $puerta->delete();

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta eliminada exitosamente.');
    }

    /**
     * Obtener estados de conexión de las puertas (API)
     * Devuelve JSON con los estados de conexión sin recargar la página
     */
    public function obtenerEstadosConexion(Request $request)
    {
        $this->authorize('viewAny', Puerta::class);

        $pisoId = $request->query('piso_id');
        $puertaIds = $request->query('puerta_ids'); // Array de IDs específicos

        $query = Puerta::query()
            ->where(function ($q) {
                $q->whereNotNull('ip_entrada')
                    ->orWhereNotNull('ip_salida');
            });

        // Filtrar por piso si se proporciona
        if ($pisoId) {
            $query->where('piso_id', $pisoId);
        }

        // Filtrar por IDs específicos si se proporcionan
        if ($puertaIds && is_array($puertaIds)) {
            $query->whereIn('id', $puertaIds);
        }

        $puertas = $query->get();
        $estados = [];

        foreach ($puertas as $puerta) {
            // Obtener estado desde caché (15 minutos) o verificar si no existe
            $cacheKey = "puerta_conexion_{$puerta->id}";
            $estaConectada = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($puerta) {
                return $puerta->estaConectada();
            });

            $estados[$puerta->id] = $estaConectada;
        }

        return response()->json([
            'success' => true,
            'estados' => $estados,
        ]);
    }

    /**
     * Refrescar estados de conexión de las puertas (fuerza nueva verificación)
     * Limpia el caché y devuelve JSON con los nuevos estados
     */
    public function refrescarConexiones(Request $request)
    {
        $this->authorize('viewAny', Puerta::class);

        $pisoId = $request->input('piso_id');
        $puertaIds = $request->input('puerta_ids'); // Array de IDs específicos

        $query = Puerta::query()
            ->where(function ($q) {
                $q->whereNotNull('ip_entrada')
                    ->orWhereNotNull('ip_salida');
            });

        // Filtrar por piso si se proporciona
        if ($pisoId) {
            $query->where('piso_id', $pisoId);
        }

        // Filtrar por IDs específicos si se proporcionan
        if ($puertaIds && is_array($puertaIds)) {
            $query->whereIn('id', $puertaIds);
        }

        $puertas = $query->get();
        $estados = [];

        // Limpiar caché y preparar targets de verificación (entrada/salida)
        $targets = [];
        foreach ($puertas as $puerta) {
            $cacheKeyEntrada = "puerta_conexion_entrada_{$puerta->id}";
            $cacheKeySalida = "puerta_conexion_salida_{$puerta->id}";
            Cache::forget($cacheKeyEntrada);
            Cache::forget($cacheKeySalida);

            if ($puerta->ip_entrada) {
                $targets[] = ['puerta_id' => (int) $puerta->id, 'lado' => 'entrada', 'ip' => (string) $puerta->ip_entrada];
            }
            if ($puerta->ip_salida) {
                $targets[] = ['puerta_id' => (int) $puerta->id, 'lado' => 'salida', 'ip' => (string) $puerta->ip_salida];
            }

            // Inicializar estructura para que el frontend no reviente si no hay IP
            $estados[$puerta->id] = [
                'entrada' => $puerta->ip_entrada ? null : null,
                'salida' => $puerta->ip_salida ? null : null,
            ];
        }

        // Ejecutar verificación de forma concurrente (cuando sea posible) para evitar bloquear demasiado
        $resultados = $this->checkTcpTargets($targets, self::PUERTA_TCP_PORT, self::PUERTA_TCP_TIMEOUT_SECONDS);

        foreach ($resultados as $r) {
            $puertaId = $r['puerta_id'];
            $lado = $r['lado']; // entrada | salida
            $ok = $r['ok'];     // bool

            $cacheKey = "puerta_conexion_{$lado}_{$puertaId}";
            Cache::put($cacheKey, $ok, now()->addMinutes(15));

            if (!isset($estados[$puertaId])) {
                $estados[$puertaId] = ['entrada' => null, 'salida' => null];
            }
            $estados[$puertaId][$lado] = $ok;
        }

        return response()->json([
            'success' => true,
            'estados' => $estados,
            'message' => 'Estados de conexión actualizados',
        ]);
    }

    /**
     * Verifica conectividad TCP a múltiples IPs de forma concurrente (si sockets está disponible).
     *
     * @param array<int, array{puerta_id:int,lado:string,ip:string}> $targets
     * @return array<int, array{puerta_id:int,lado:string,ip:string,ok:bool}>
     */
    private function checkTcpTargets(array $targets, int $port, float $timeoutSeconds): array
    {
        // Si no hay targets, no hay nada que verificar
        if (count($targets) === 0) {
            return [];
        }

        // Si no existe sockets, hacemos fallback secuencial con timeout bajo
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
                    'lado' => $t['lado'],
                    'ip' => $t['ip'],
                    'ok' => $ok,
                ];
            }
            return $out;
        }

        $streams = [];
        $meta = [];
        $out = [];

        $start = microtime(true);

        foreach ($targets as $idx => $t) {
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
                    'lado' => $t['lado'],
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

            // Streams "writable" => connect success o failure. Diferenciar con SO_ERROR.
            foreach ($write as $s) {
                $key = (int) $s;
                $t = $meta[$key] ?? null;
                if (!$t) {
                    @fclose($s);
                    unset($streams[$key], $meta[$key]);
                    continue;
                }

                $ok = false;
                $sock = @socket_import_stream($s);
                if ($sock !== false) {
                    $err = @socket_get_option($sock, SOL_SOCKET, SO_ERROR);
                    $ok = ($err === 0);
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

            // Streams con excepción => fallo
            foreach ($except as $s) {
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
                @fclose($s);
                unset($streams[$key], $meta[$key]);
            }
        }

        // Lo que quede pendiente al timeout => false
        foreach ($streams as $key => $s) {
            $t = $meta[$key] ?? null;
            if ($t) {
                $out[] = [
                    'puerta_id' => (int) $t['puerta_id'],
                    'lado' => (string) $t['lado'],
                    'ip' => (string) $t['ip'],
                    'ok' => false,
                ];
            }
            @fclose($s);
        }

        return $out;
    }

    /**
     * Enviar comando de reinicio a las Raspberry Pi de una puerta
     */
    public function reiniciarPuerta(Puerta $puerta)
    {
        $this->authorize('reboot', $puerta);

        $resultados = [
            'entrada' => null,
            'salida' => null,
        ];

        // Reiniciar Raspberry Pi de entrada si tiene IP
        if ($puerta->ip_entrada) {
            try {
                $resultados['entrada'] = $this->enviarComandoReinicio($puerta->ip_entrada);
            } catch (\Exception $e) {
                $resultados['entrada'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Reiniciar Raspberry Pi de salida si tiene IP
        if ($puerta->ip_salida) {
            try {
                $resultados['salida'] = $this->enviarComandoReinicio($puerta->ip_salida);
            } catch (\Exception $e) {
                $resultados['salida'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'resultados' => $resultados,
            'message' => 'Comando de reinicio enviado',
        ]);
    }

    /**
     * Abrir/cerrar puerta manualmente (toggle)
     */
    public function togglePuerta(Puerta $puerta, Request $request)
    {
        $this->authorize('toggle', $puerta);

        $tipo = $request->input('tipo', 'entrada'); // 'entrada' o 'salida'
        $ip = $tipo === 'entrada' ? $puerta->ip_entrada : $puerta->ip_salida;

        if (!$ip) {
            return response()->json([
                'success' => false,
                'error' => "IP de {$tipo} no configurada",
            ], 400);
        }

        try {
            $resultado = $this->enviarComandoToggle($ip);
            return response()->json([
                'success' => true,
                'resultado' => $resultado,
                'tipo' => $tipo,
                'message' => 'Comando de apertura/cierre enviado',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estado de apertura manual de una puerta
     */
    public function estadoPuerta(Puerta $puerta, Request $request)
    {
        $this->authorize('view', $puerta);

        $tipo = $request->query('tipo', 'entrada'); // 'entrada' o 'salida'
        $ip = $tipo === 'entrada' ? $puerta->ip_entrada : $puerta->ip_salida;

        if (!$ip) {
            return response()->json([
                'success' => false,
                'error' => "IP de {$tipo} no configurada",
            ], 400);
        }

        try {
            $estado = $this->obtenerEstadoPuerta($ip);
            return response()->json([
                'success' => true,
                'estado' => $estado,
                'tipo' => $tipo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enviar comando de toggle (abrir/cerrar) a una IP específica
     */
    private function enviarComandoToggle(string $ip): array
    {
        $port = (int) (config('app.door_emergency_port') ?? env('DOOR_EMERGENCY_PORT', 8000));
        $apiKey = (string) (config('app.door_api_key') ?? env('DOOR_API_KEY', ''));
        $url = "http://{$ip}:{$port}/api/door/toggle";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . $apiKey,
            'X-DEVICE-KEY: ' . $apiKey, // Compatibilidad con ingreso.py
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "Error de conexión: {$error}",
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'manual_open' => $data['manual_open'] ?? false,
                'message' => $data['message'] ?? 'Comando enviado exitosamente',
                'http_code' => $httpCode,
            ];
        }

        return [
            'success' => false,
            'error' => "Error HTTP: {$httpCode}",
            'response' => $response,
        ];
    }

    /**
     * Obtener estado de apertura manual de una IP específica
     */
    private function obtenerEstadoPuerta(string $ip): array
    {
        $port = (int) (config('app.door_emergency_port') ?? env('DOOR_EMERGENCY_PORT', 8000));
        $apiKey = (string) (config('app.door_api_key') ?? env('DOOR_API_KEY', ''));
        $url = "http://{$ip}:{$port}/api/door/status";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-API-KEY: ' . $apiKey,
            'X-DEVICE-KEY: ' . $apiKey, // Compatibilidad con ingreso.py
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            return [
                'manual_open' => false,
                'error' => $error ?: "HTTP {$httpCode}",
            ];
        }

        $data = json_decode($response, true);
        return [
            'manual_open' => $data['manual_open'] ?? false,
        ];
    }

    /**
     * Enviar comando de reinicio a una IP específica
     * Nota: Esto requiere que la Raspberry Pi tenga un servicio HTTP que acepte comandos de reinicio
     */
    private function enviarComandoReinicio(string $ip): array
    {
        // Intentar enviar comando por HTTP (asumiendo que hay un servicio en la Raspberry Pi)
        // Si la Raspberry Pi tiene un endpoint que acepta comandos, lo usamos
        $port = (int) (config('app.door_emergency_port') ?? env('DOOR_EMERGENCY_PORT', 8000));
        $apiKey = (string) (config('app.door_api_key') ?? env('DOOR_API_KEY', ''));
        $url = "http://{$ip}:{$port}/reboot";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => 'reboot']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . $apiKey,
            'X-DEVICE-KEY: ' . $apiKey, // Compatibilidad con ingreso.py
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "Error de conexión: {$error}",
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'Comando de reinicio enviado exitosamente',
                'http_code' => $httpCode,
            ];
        }

        return [
            'success' => false,
            'error' => "Error HTTP: {$httpCode}",
            'response' => $response,
        ];
    }
}
