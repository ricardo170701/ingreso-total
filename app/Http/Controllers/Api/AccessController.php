<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\CodigoQr;
use App\Models\Puerta;
use App\Models\TarjetaNfc;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    /**
     * Verifica un QR escaneado contra una puerta (codigo_fisico) y registra el evento.
     * El token recibido se hashea con sha256 y se busca en codigos_qr.codigo.
     *
     * @OA\Post(
     *   path="/api/access/verify",
     *   tags={"Acceso"},
     *   summary="Verificar acceso por QR (lector)",
     *   @OA\Parameter(
     *     name="X-DEVICE-KEY",
     *     in="header",
     *     required=true,
     *     @OA\Schema(type="string"),
     *     description="Obligatorio: llave del dispositivo lector (debe coincidir con ACCESS_DEVICE_KEY en .env)"
     *   ),
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"token","codigo_fisico"},
     *     @OA\Property(property="token", type="string", example="(valor leído del QR)"),
     *     @OA\Property(property="codigo_fisico", type="string", example="P2-ENT"),
     *     @OA\Property(
     *       property="tipo_evento",
     *       type="string",
     *       nullable=true,
     *       description="entrada o salida. Si no se envía, se asume entrada.",
     *       example="entrada"
     *     ),
     *     @OA\Property(
     *       property="dispositivo_id",
     *       type="string",
     *       nullable=true,
     *       description="Identificador único de la Raspberry/lector (para analíticas y mantenimiento).",
     *       example="P1-ENT-RPI-ENTRADA"
     *     )
     *   )),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function verify(Request $request): JsonResponse
    {
        // Seguridad obligatoria por dispositivo lector (fail-closed)
        // Normalizar valores para evitar problemas por comillas/espacios en .env o en el header
        $requiredKey = env('ACCESS_DEVICE_KEY');
        $requiredKey = is_string($requiredKey) ? trim($requiredKey, " \t\n\r\0\x0B\"'") : null;
        if (!$requiredKey) {
            return response()->json(['message' => 'ACCESS_DEVICE_KEY no configurada en el servidor.'], 500);
        }

        $provided = $request->header('X-DEVICE-KEY');
        $provided = is_string($provided) ? trim($provided, " \t\n\r\0\x0B\"'") : null;
        if (!$provided || !hash_equals($requiredKey, $provided)) {
            return response()->json(['message' => 'Dispositivo no autorizado.'], 403);
        }

        $data = $request->validate([
            'token' => ['required', 'string'],
            'codigo_fisico' => ['required', 'string', 'max:50'],
            'tipo_evento' => ['nullable', 'in:entrada,salida'],
            'dispositivo_id' => ['nullable', 'string', 'max:80'],
        ]);

        $now = Carbon::now();
        $tipoEvento = $data['tipo_evento'] ?? 'entrada';
        $dispositivoId = $data['dispositivo_id'] ?? null;

        $puerta = Puerta::query()
            ->with(['piso', 'tipoPuerta'])
            ->where(function ($q) use ($data) {
                $q->where('codigo_fisico', $data['codigo_fisico'])
                    ->orWhere('codigo_fisico_salida', $data['codigo_fisico']);
            })
            ->first();

        if (!$puerta) {
            return response()->json(['permitido' => false, 'message' => 'Puerta no encontrada.'], 200);
        }

        $tokenHash = hash('sha256', $data['token']);
        $tokenCodigo = $data['token']; // Para tarjetas NFC, el código viene directo (no hasheado)

        // Intentar buscar como QR primero
        $qr = CodigoQr::query()
            ->with(['user.role', 'user.cargo'])
            ->where('codigo', $tokenHash)
            ->where('activo', true)
            ->first();

        // Si no es QR, intentar como tarjeta NFC
        $tarjetaNfc = null;
        if (!$qr) {
            $tarjetaNfc = TarjetaNfc::query()
                ->with(['user.role', 'user.cargo'])
                ->where('codigo', $tokenCodigo)
                ->where('activo', true)
                ->first();
        }

        if (!$qr && !$tarjetaNfc) {
            $this->registrarAcceso(null, $puerta->id, null, null, false, 'QR/Tarjeta inválido', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'QR/Tarjeta NFC inválido.'], 200);
        }

        // Determinar usuario por tipo de credencial
        $qrId = $qr?->id;
        $tarjetaNfcId = $tarjetaNfc?->id;
        $user = $qr ? $qr->user : $tarjetaNfc?->user;

        // Tarjeta NFC sin asignar (o usuario eliminado)
        if (!$user) {
            $motivo = $qr ? 'QR sin usuario' : 'Tarjeta NFC sin asignar';
            $this->registrarAcceso(null, $puerta->id, $qrId, $tarjetaNfcId, false, $motivo, $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => $motivo . '.'], 200);
        }

        if (isset($user->activo) && $user->activo === false) {
            $this->registrarAcceso($user->id, $puerta->id, $qrId, $tarjetaNfcId, false, 'Usuario inactivo', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Usuario inactivo.'], 200);
        }

        // Para funcionarios: verificar solo la fecha de expiración del usuario
        // - Si tiene fecha_expiracion: validar que no haya expirado
        // - Si NO tiene fecha_expiracion (contrato indefinido): permitir acceso hasta que se marque como inactivo
        // Para visitantes: verificar la fecha de expiración de la credencial (QR/Tarjeta)
        // Tipos de vinculación (compatibilidad: 'funcionario' legado)
        $userRole = $user->role?->name ?? null;
        // Compatibilidad: en algunos entornos antiguos existía "contratista" y luego se renombró a "proveedor"
        $staffRoles = ['servidor_publico', 'proveedor', 'contratista', 'funcionario'];
        $isStaff = in_array($userRole, $staffRoles, true);

        if ($isStaff) {
            if ($user->fecha_expiracion && Carbon::parse($user->fecha_expiracion)->lt($now->copy()->startOfDay())) {
                $this->registrarAcceso($user->id, $puerta->id, $qrId, $tarjetaNfcId, false, 'Usuario expirado', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json(['permitido' => false, 'message' => 'Usuario expirado.'], 200);
            }
        } else {
            $fechaExpiracion = $qr ? $qr->fecha_expiracion : $tarjetaNfc?->fecha_expiracion;
            if ($fechaExpiracion && Carbon::parse($fechaExpiracion)->lt($now)) {
                $msg = $qr ? 'QR expirado' : 'Tarjeta NFC expirada';
                $this->registrarAcceso($user->id, $puerta->id, $qrId, $tarjetaNfcId, false, $msg, $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json(['permitido' => false, 'message' => $msg . '.'], 200);
            }

            // Visitantes: solo pueden ingresar entre 5:00 y 19:00 (hora Colombia)
            $colombiaNow = Carbon::now('America/Bogota');
            $hora = (int) $colombiaNow->format('H');
            $minuto = (int) $colombiaNow->format('i');
            $minutosDesdeMedianoche = $hora * 60 + $minuto;
            $inicioPermitido = 5 * 60;   // 5:00
            $finPermitido = 19 * 60;     // 19:00 (7 PM) — a partir de las 19:00 ya no se permite
            if ($minutosDesdeMedianoche < $inicioPermitido || $minutosDesdeMedianoche >= $finPermitido) {
                $motivo = 'Los visitantes solo pueden ingresar entre 5:00 y 19:00 (hora Colombia).';
                $this->registrarAcceso($user->id, $puerta->id, $qrId, $tarjetaNfcId, false, $motivo, $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json(['permitido' => false, 'message' => $motivo], 200);
            }
        }

        // Puerta de discapacidad: requiere usuario discapacitado, además de permiso
        if ($puerta->requiere_discapacidad && !$user->es_discapacitado) {
            $this->registrarAcceso($user->id, $puerta->id, $qr?->id, $tarjetaNfc?->id, false, 'Requiere discapacidad', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Acceso denegado (discapacidad requerida).'], 200);
        }

        // Puerta de datacenter: requiere permiso especial access_datacenter
        if ($puerta->requiere_permiso_datacenter && !$user->hasPermission('access_datacenter')) {
            $this->registrarAcceso($user->id, $puerta->id, $qr?->id, $tarjetaNfc?->id, false, 'Requiere permiso datacenter', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Acceso denegado (requiere permiso de datacenter).'], 200);
        }

        // Solo servidores públicos / proveedores: visitantes denegados
        if ($puerta->solo_servidores_publicos && !$isStaff) {
            $this->registrarAcceso($user->id, $puerta->id, $qr?->id, $tarjetaNfc?->id, false, 'Solo servidores públicos o proveedores', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Acceso denegado (solo servidores públicos o proveedores).'], 200);
        }

        // Estado entrada/salida: solo para MOLINETES.
        // Molinetes: entrada requiere haber registrado salida antes; salida requiere entrada previa.
        // Puertas: solo registran entrada, sin restricción por salida (no se valida ciclo).
        if ($puerta->esMolinete()) {
            $lastOk = Acceso::query()
                ->where('user_id', $user->id)
                ->where('permitido', true)
                ->whereIn('tipo_evento', ['entrada', 'salida'])
                ->orderByDesc('fecha_acceso')
                ->orderByDesc('id')
                ->first();

            if ($tipoEvento === 'entrada' && $lastOk?->tipo_evento === 'entrada') {
                $motivo = 'Usuario ya se encuentra dentro (falta registrar salida)';
                $this->registrarAcceso($user->id, $puerta->id, $qr?->id, $tarjetaNfc?->id, false, $motivo, $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json([
                    'permitido' => false,
                    'message' => 'Usuario ya se encuentra dentro. Registre salida para poder volver a entrar.',
                ], 200);
            }
            if ($tipoEvento === 'salida' && (!$lastOk || $lastOk->tipo_evento !== 'entrada')) {
                $motivo = 'Usuario no tiene entrada activa (no puede registrar salida)';
                $this->registrarAcceso($user->id, $puerta->id, $qr?->id, $tarjetaNfc?->id, false, $motivo, $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json([
                    'permitido' => false,
                    'message' => 'Usuario no tiene una entrada activa. Primero registre entrada.',
                ], 200);
            }
        }

        // Determinar si el usuario es funcionario o visitante
        $userRole = $user->role?->name ?? null;
        // (ver arriba) Mantener compatibilidad proveedor/contratista
        $staffRoles = $staffRoles ?? ['servidor_publico', 'proveedor', 'contratista', 'funcionario'];
        $isStaff = in_array($userRole, $staffRoles, true);

        $permitido = false;
        $motivo = null;

        // Para funcionarios: solo permiso por puerta (cargo_puerta_acceso)
        // Para visitantes: usar reglas de la credencial (QR/Tarjeta NFC)
        if ($isStaff) {
            if (!$user->cargo_id) {
                $permitido = false;
                $motivo = 'Sin cargo asignado';
            } else {
                $cargoRule = DB::table('cargo_puerta_acceso')
                    ->where('cargo_id', $user->cargo_id)
                    ->where('puerta_id', $puerta->id)
                    ->first();

                if (!$cargoRule) {
                    $permitido = false;
                    $motivo = 'Sin permiso para esta puerta';
                } else {
                    $permitido = $this->ruleAllows($cargoRule, $now);
                    $motivo = $permitido ? null : 'Fuera de horario (cargo)';
                }
            }
        } else {
            // Visitante: usar reglas de la credencial (QR o Tarjeta NFC)
            $hasDoorRules = false;
            $rule = null;

            if ($qr) {
                $hasDoorRules = DB::table('codigo_qr_puerta_acceso')
                    ->where('codigo_qr_id', $qr->id)
                    ->exists();
                $rule = DB::table('codigo_qr_puerta_acceso')
                    ->where('codigo_qr_id', $qr->id)
                    ->where('puerta_id', $puerta->id)
                    ->first();
            } else {
                $hasDoorRules = DB::table('tarjeta_nfc_puerta_acceso')
                    ->where('tarjeta_nfc_id', $tarjetaNfc->id)
                    ->exists();
                $rule = DB::table('tarjeta_nfc_puerta_acceso')
                    ->where('tarjeta_nfc_id', $tarjetaNfc->id)
                    ->where('puerta_id', $puerta->id)
                    ->first();
            }

            if ($hasDoorRules) {
                if (!$rule) {
                    $permitido = false;
                    $motivo = ($qr ? 'QR' : 'Tarjeta NFC') . ' no autorizado para esta puerta';
                } else {
                    $permitido = $this->ruleAllows($rule, $now);
                    $motivo = $permitido ? null : 'Fuera de horario (' . ($qr ? 'QR' : 'Tarjeta NFC') . ')';
                }
            } elseif ($rule) {
                // Compatibilidad: si existe regla puntual aunque no haya otras, la respetamos.
                $permitido = $this->ruleAllows($rule, $now);
                $motivo = $permitido ? null : 'Fuera de horario (' . ($qr ? 'QR' : 'Tarjeta NFC') . ')';
            } else {
                // Sin reglas de puertas: denegar (visitantes deben tener reglas explícitas)
                $permitido = false;
                $motivo = 'Sin permisos configurados para esta puerta';
            }
        }

        $this->registrarAcceso(
            $user->id,
            $puerta->id,
            $qr?->id,
            $tarjetaNfc?->id,
            $permitido,
            $motivo,
            $data['codigo_fisico'],
            $tipoEvento,
            $dispositivoId,
            $now
        );

        // Puertas con solo ip_entrada (sin ip_salida): el egreso se hace con botón físico,
        // así que registramos una salida automática para no dejar la "entrada" activa.
        if (
            $permitido
            && $tipoEvento === 'entrada'
            && !empty($puerta->ip_entrada)
            && empty($puerta->ip_salida)
        ) {
            $this->registrarAcceso(
                $user->id,
                $puerta->id,
                $qr?->id,
                $tarjetaNfc?->id,
                true,
                null,
                $data['codigo_fisico'],
                'salida',
                $dispositivoId,
                $now->copy()->addSecond(),
                'Auto-salida: puerta sin lector de salida (solo ip_entrada).'
            );
        }

        // Determinar el tiempo de apertura según si el usuario es discapacitado
        $tiempoApertura = $puerta->tiempo_apertura ?? 5; // Valor por defecto si no está configurado
        if ($permitido && $user->es_discapacitado && $puerta->tiempo_discapacitados) {
            $tiempoApertura = $puerta->tiempo_discapacitados;
        }

        return response()->json([
            'permitido' => $permitido,
            'message' => $permitido ? 'Acceso permitido.' : ('Acceso denegado. ' . ($motivo ?? '')),
            'tiempo_apertura' => $permitido ? $tiempoApertura : null, // Solo devolver tiempo si el acceso es permitido
            'data' => [
                'user_id' => $user->id,
                'puerta_id' => $puerta->id,
                'codigo_fisico' => $data['codigo_fisico'],
                'codigo_qr_id' => $qr?->id,
                'tarjeta_nfc_id' => $tarjetaNfc?->id,
                'tipo_evento' => $tipoEvento,
                'dispositivo_id' => $dispositivoId,
                'fecha' => $now->toIso8601String(),
            ],
        ]);
    }

    private function registrarAcceso(
        ?int $userId,
        int $puertaId,
        ?int $codigoQrId,
        ?int $tarjetaNfcId,
        bool $permitido,
        ?string $motivo,
        ?string $lectorId,
        string $tipoEventoIntentado = 'entrada',
        ?string $dispositivoId = null,
        ?Carbon $fechaAcceso = null,
        ?string $observacionesPermitido = null
    ): void {
        Acceso::query()->create([
            // user_id será nullable (migración adicional) para registrar intentos con QR/Tarjeta inválido
            'user_id' => $userId,
            'puerta_id' => $puertaId,
            'codigo_qr_id' => $codigoQrId,
            'tarjeta_nfc_id' => $tarjetaNfcId,
            'tipo_evento' => $permitido ? $tipoEventoIntentado : 'denegado',
            'fecha_acceso' => $fechaAcceso ?? Carbon::now(),
            'permitido' => $permitido,
            'lector_id' => $lectorId,
            'dispositivo_id' => $dispositivoId,
            'motivo_denegacion' => $motivo,
            'observaciones' => $permitido ? $observacionesPermitido : ('Intentó: ' . $tipoEventoIntentado),
        ]);
    }

    private function ruleAllows(object $rule, Carbon $now): bool
    {
        if (property_exists($rule, 'activo') && (int) $rule->activo === 0) {
            return false;
        }

        // Fechas (usar copy() para no mutar $now; si no, fecha_acceso quedaría en 00:00:00)
        $startOfDay = $now->copy()->startOfDay();
        if (!empty($rule->fecha_inicio) && Carbon::parse($rule->fecha_inicio)->gt($startOfDay)) {
            return false;
        }
        if (!empty($rule->fecha_fin) && Carbon::parse($rule->fecha_fin)->lt($startOfDay)) {
            return false;
        }

        // Días semana: "1,2,3,4,5"
        $dias = $rule->dias_semana ?? null;
        if ($dias) {
            $set = array_filter(array_map('trim', explode(',', (string) $dias)));
            $dow = (string) $now->dayOfWeekIso; // 1-7
            if (!in_array($dow, $set, true)) {
                return false;
            }
        }

        // Horas
        $hi = $rule->hora_inicio ?? null;
        $hf = $rule->hora_fin ?? null;
        if ($hi || $hf) {
            $t = $now->format('H:i:s');
            $hiNorm = $hi ? $this->normalizeTime((string) $hi) : null;
            $hfNorm = $hf ? $this->normalizeTime((string) $hf) : null;

            if ($hiNorm && $t < $hiNorm) {
                return false;
            }
            if ($hfNorm && $t > $hfNorm) {
                return false;
            }
        }

        return true;
    }

    private function normalizeTime(string $time): string
    {
        // MySQL TIME suele venir como "HH:MM:SS". Si viene "HH:MM", lo convertimos a "HH:MM:SS".
        $time = trim($time);
        $parts = explode(':', $time);
        if (count($parts) === 2) {
            return $time . ':00';
        }
        return $time;
    }
}
