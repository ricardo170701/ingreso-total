<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\CodigoQr;
use App\Models\Puerta;
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
        $requiredKey = env('ACCESS_DEVICE_KEY');
        if (!$requiredKey) {
            return response()->json(['message' => 'ACCESS_DEVICE_KEY no configurada en el servidor.'], 500);
        }

        $provided = $request->header('X-DEVICE-KEY');
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
            ->with('piso')
            ->where(function ($q) use ($data) {
                $q->where('codigo_fisico', $data['codigo_fisico'])
                    ->orWhere('codigo_fisico_salida', $data['codigo_fisico']);
            })
            ->first();

        if (!$puerta) {
            return response()->json(['permitido' => false, 'message' => 'Puerta no encontrada.'], 200);
        }

        $tokenHash = hash('sha256', $data['token']);

        $qr = CodigoQr::query()
            ->with(['user.role', 'user.cargo'])
            ->where('codigo', $tokenHash)
            ->where('activo', true)
            ->first();

        if (!$qr) {
            $this->registrarAcceso(null, $puerta->id, null, false, 'QR inválido', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'QR inválido.'], 200);
        }

        $user = $qr->user;
        if (!$user || (isset($user->activo) && $user->activo === false)) {
            $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, false, 'Usuario inactivo', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Usuario inactivo.'], 200);
        }

        // Para funcionarios: verificar solo la fecha de expiración del usuario
        // - Si tiene fecha_expiracion: validar que no haya expirado
        // - Si NO tiene fecha_expiracion (contrato indefinido): permitir acceso hasta que se marque como inactivo
        // Para visitantes: verificar la fecha de expiración del QR (15 días)
        $userRole = $user->role?->name ?? null;
        if ($userRole === 'funcionario') {
            // Funcionarios: el QR está activo hasta la fecha de expiración del usuario (si existe)
            // Si el usuario tiene contrato indefinido (sin fecha_expiracion), puede acceder hasta que se marque como inactivo
            if ($user->fecha_expiracion && Carbon::parse($user->fecha_expiracion)->lt($now->startOfDay())) {
                $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, false, 'Usuario expirado', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json(['permitido' => false, 'message' => 'Usuario expirado.'], 200);
            }
        } else {
            // Visitantes: verificar la fecha de expiración del QR (15 días)
            if ($qr->fecha_expiracion && $qr->fecha_expiracion->lt($now)) {
                $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, false, 'QR expirado', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
                return response()->json(['permitido' => false, 'message' => 'QR expirado.'], 200);
            }
        }

        // Puerta de discapacidad: requiere usuario discapacitado, además de permiso
        if ($puerta->requiere_discapacidad && !$user->es_discapacitado) {
            $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, false, 'Requiere discapacidad', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Acceso denegado (discapacidad requerida).'], 200);
        }

        // Estado entrada/salida:
        // - entrada: si la última operación permitida fue entrada, se debe registrar salida antes de otra entrada
        // - salida: solo se permite si la última operación permitida fue entrada
        $lastOk = Acceso::query()
            ->where('codigo_qr_id', $qr->id)
            ->where('permitido', true)
            ->whereIn('tipo_evento', ['entrada', 'salida'])
            ->orderByDesc('fecha_acceso')
            ->first();

        if ($tipoEvento === 'entrada' && $lastOk?->tipo_evento === 'entrada') {
            $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, false, 'Entrada ya registrada (falta salida)', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'Entrada ya registrada. Debe registrarse salida antes de otra entrada.'], 200);
        }
        if ($tipoEvento === 'salida' && (!$lastOk || $lastOk->tipo_evento !== 'entrada')) {
            $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, false, 'No hay entrada activa', $data['codigo_fisico'], $tipoEvento, $dispositivoId);
            return response()->json(['permitido' => false, 'message' => 'No hay una entrada activa para registrar salida.'], 200);
        }

        // Si este QR tiene puertas explícitas (código_qr_puerta_acceso), entonces restringe SOLO a esas puertas
        $qrHasDoorRules = DB::table('codigo_qr_puerta_acceso')
            ->where('codigo_qr_id', $qr->id)
            ->exists();

        $qrRule = DB::table('codigo_qr_puerta_acceso')
            ->where('codigo_qr_id', $qr->id)
            ->where('puerta_id', $puerta->id)
            ->first();

        $permitido = false;
        $motivo = null;

        if ($qrHasDoorRules) {
            if (!$qrRule) {
                $permitido = false;
                $motivo = 'QR no autorizado para esta puerta';
            } else {
                $permitido = $this->ruleAllows($qrRule, $now);
                $motivo = $permitido ? null : 'Fuera de horario (QR)';
            }
        } elseif ($qrRule) {
            // Compatibilidad: si existe regla QR puntual aunque no haya otras, la respetamos.
            $permitido = $this->ruleAllows($qrRule, $now);
            $motivo = $permitido ? null : 'Fuera de horario (QR)';
        } else {
            // 2) Permiso por cargo->piso (si el cargo tiene permiso al piso de la puerta)
            if (!$user->cargo_id) {
                $permitido = false;
                $motivo = 'Sin cargo asignado';
            } elseif (!$puerta->piso_id) {
                $permitido = false;
                $motivo = 'Puerta sin piso asignado';
            } else {
                $cargoRule = DB::table('cargo_piso_acceso')
                    ->where('cargo_id', $user->cargo_id)
                    ->where('piso_id', $puerta->piso_id)
                    ->first();

                if (!$cargoRule) {
                    $permitido = false;
                    $motivo = 'Sin permiso para el piso';
                } else {
                    $permitido = $this->ruleAllows($cargoRule, $now);
                    $motivo = $permitido ? null : 'Fuera de horario (cargo)';
                }
            }
        }

        $this->registrarAcceso($qr->user_id, $puerta->id, $qr->id, $permitido, $motivo, $data['codigo_fisico'], $tipoEvento, $dispositivoId);

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
                'user_id' => $qr->user_id,
                'puerta_id' => $puerta->id,
                'codigo_fisico' => $data['codigo_fisico'],
                'codigo_qr_id' => $qr->id,
                'tipo_evento' => $tipoEvento,
                'dispositivo_id' => $dispositivoId,
                'fecha' => $now->toIso8601String(),
            ],
        ]);
    }

    private function registrarAcceso(?int $userId, int $puertaId, ?int $codigoQrId, bool $permitido, ?string $motivo, ?string $lectorId, string $tipoEventoIntentado = 'entrada', ?string $dispositivoId = null): void
    {
        Acceso::query()->create([
            // user_id será nullable (migración adicional) para registrar intentos con QR inválido
            'user_id' => $userId,
            'puerta_id' => $puertaId,
            'codigo_qr_id' => $codigoQrId,
            'tipo_evento' => $permitido ? $tipoEventoIntentado : 'denegado',
            'fecha_acceso' => Carbon::now(),
            'permitido' => $permitido,
            'lector_id' => $lectorId,
            'dispositivo_id' => $dispositivoId,
            'motivo_denegacion' => $motivo,
            'observaciones' => $permitido ? null : ('Intentó: ' . $tipoEventoIntentado),
        ]);
    }

    private function ruleAllows(object $rule, Carbon $now): bool
    {
        if (property_exists($rule, 'activo') && (int) $rule->activo === 0) {
            return false;
        }

        // Fechas
        if (!empty($rule->fecha_inicio) && Carbon::parse($rule->fecha_inicio)->gt($now->startOfDay())) {
            return false;
        }
        if (!empty($rule->fecha_fin) && Carbon::parse($rule->fecha_fin)->lt($now->startOfDay())) {
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
