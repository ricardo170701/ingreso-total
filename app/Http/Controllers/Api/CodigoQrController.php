<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCodigoQrRequest;
use App\Models\CodigoQr;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CodigoQrController extends Controller
{
    /**
     * Genera un QR temporal (15 días).
     * En BD se guarda sha256(token) en `codigos_qr.codigo` (hash) y se retorna el token plano para convertirlo a QR.
     * Nota: el token retornado es corto (10 caracteres) para facilitar lectura en dispositivos.
     *
     * @OA\Post(
     *   path="/api/qrs",
     *   tags={"QR"},
     *   summary="Generar QR temporal (15 días)",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"user_id"},
     *     @OA\Property(property="user_id", type="integer", example=1),
     *     @OA\Property(
     *       property="puertas",
     *       type="array",
     *       nullable=true,
     *       description="Obligatorio si el usuario destino es visitante. Opcional para el resto (se usará el cargo).",
     *       @OA\Items(type="integer"),
     *       example={1,2}
     *     ),
     *     @OA\Property(property="hora_inicio", type="string", nullable=true, example="08:00"),
     *     @OA\Property(property="hora_fin", type="string", nullable=true, example="18:00"),
     *     @OA\Property(property="dias_semana", type="string", nullable=true, example="1,2,3,4,5"),
     *     @OA\Property(property="fecha_inicio", type="string", format="date", nullable=true, example=null),
     *     @OA\Property(property="fecha_fin", type="string", format="date", nullable=true, example=null)
     *   )),
     *   @OA\Response(response=201, description="Creado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(GenerateCodigoQrRequest $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        // Visitante: solo puede ver/usar su QR, no generar
        if (($actor?->role?->name ?? null) === 'visitante') {
            return response()->json(['message' => 'Como visitante no puedes generar QR.'], 403);
        }

        /** @var User $targetUser */
        $targetUser = User::query()->with('role')->findOrFail($data['user_id']);

        $targetRole = $targetUser->role?->name;

        // Autorización por permisos (los roles solo representan tipo de usuario: funcionario/visitante)
        if (!$actor || !$actor->hasPermission('create_ingreso')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Si está generando para otro usuario, requiere permiso explícito
        if ($actor->id !== $targetUser->id && !$actor->hasPermission('create_ingreso_otros')) {
            return response()->json(['message' => 'No autorizado para generar QR para otros usuarios.'], 403);
        }

        $puertas = $data['puertas'] ?? [];
        $pisos = $data['pisos'] ?? [];
        $hasPuertas = is_array($puertas) && count($puertas) > 0;
        $hasPisos = is_array($pisos) && count($pisos) > 0;

        // Regla: visitantes deben traer pisos (se expanden a puertas en backend)
        if ($targetRole === 'visitante' && !$hasPisos) {
            return response()->json([
                'message' => 'Para visitantes debes seleccionar al menos un piso.',
                'errors' => ['pisos' => ['Para visitantes debes seleccionar al menos un piso.']],
            ], 422);
        }

        if ($targetRole === 'visitante' && $hasPisos) {
            $puertasEnPisos = \App\Models\Puerta::query()
                ->where('activo', true)
                ->whereIn('piso_id', $pisos)
                ->count();
            if ($puertasEnPisos <= 0) {
                return response()->json([
                    'message' => 'Los pisos seleccionados no tienen puertas activas.',
                    'errors' => ['pisos' => ['Los pisos seleccionados no tienen puertas activas.']],
                ], 422);
            }
        }

        // Regla: si se genera para visitante y es "para otros", debe indicar departamento destino
        if ($actor->id !== $targetUser->id && $targetRole === 'visitante' && empty($data['departamento_id'])) {
            return response()->json([
                'message' => 'Para visitantes debes seleccionar el departamento destino.',
                'errors' => ['departamento_id' => ['Para visitantes debes seleccionar el departamento destino.']],
            ], 422);
        }

        // Regla: para no visitantes, si no envían puertas, el usuario debe tener cargo (se validará por cargo->puerta)
        if ($targetRole !== 'visitante' && !$hasPuertas && !$targetUser->cargo_id) {
            return response()->json([
                'message' => 'El usuario no tiene cargo asignado. Asigna un cargo o envía puertas explícitas.',
                'errors' => ['cargo_id' => ['El usuario no tiene cargo asignado.']],
            ], 422);
        }

        $now = Carbon::now();

        // Para funcionarios:
        // - Si tiene fecha_expiracion: usar esa fecha
        // - Si NO tiene fecha_expiracion (contrato indefinido): null (el acceso se controla solo por campo 'activo')
        // Para visitantes: mantener 15 días
        if ($targetRole === 'funcionario') {
            if ($targetUser->fecha_expiracion) {
                $expiresAt = Carbon::parse($targetUser->fecha_expiracion)->endOfDay();
            } else {
                // Contrato indefinido: el QR no expira, el acceso se controla solo por el campo 'activo' del usuario
                $expiresAt = null;
            }
        } else {
            $expiresAt = $now->copy()->addDays(15);
        }

        // Token opaco (para QR) + hash (para BD)
        // Token corto (10 chars), evitando caracteres confusos para lectores (0/O, 1/I/L).
        // Se guarda sha256(token) en BD para no exponer el hash directamente.
        do {
            $plainToken = $this->makeShortToken(10);
            $tokenHash = hash('sha256', $plainToken);
        } while (CodigoQr::query()->where('codigo', $tokenHash)->exists());

        $qr = null;

        DB::transaction(function () use (&$qr, $targetUser, $actor, $tokenHash, $now, $expiresAt, $data, $plainToken) {
            // Regla: al generar uno nuevo, desactivar cualquier QR activo previo del usuario destino
            CodigoQr::query()
                ->where('user_id', $targetUser->id)
                ->activos()
                ->update([
                    'activo' => false,
                    'uso_actual' => 'expirado',
                    'updated_at' => now(),
                ]);

            $qr = new CodigoQr();
            $qr->user_id = $targetUser->id;
            $qr->departamento_id = $data['departamento_id'] ?? null;
            $qr->codigo = $tokenHash;
            $qr->setTokenOriginal($plainToken); // Encriptar y guardar el token
            $qr->fecha_generacion = $now;
            $qr->fecha_expiracion = $expiresAt;
            $qr->usado = false;
            $qr->activo = true;
            $qr->generado_por = $actor?->id;
            $qr->tipo = 'temporal';
            $qr->uso_actual = 'pendiente';
            $qr->intentos_fallidos = 0;
            $qr->save();

            // Si envían puertas explícitas, se guardan reglas QR->puerta (sino se evalúa por cargo en verificación)
            $puertas = $data['puertas'] ?? [];
            $pisos = $data['pisos'] ?? [];

            if (($targetUser->role?->name ?? null) === 'visitante' && is_array($pisos) && count($pisos) > 0) {
                $puertas = \App\Models\Puerta::query()
                    ->where('activo', true)
                    ->whereIn('piso_id', $pisos)
                    ->pluck('id')
                    ->toArray();
            }

            if (is_array($puertas) && count($puertas) > 0) {
                $pivot = [
                    'hora_inicio' => $data['hora_inicio'] ?? null,
                    'hora_fin' => $data['hora_fin'] ?? null,
                    'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
                    'fecha_inicio' => $data['fecha_inicio'] ?? null,
                    'fecha_fin' => $data['fecha_fin'] ?? null,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                foreach ($puertas as $puertaId) {
                    DB::table('codigo_qr_puerta_acceso')->updateOrInsert(
                        ['codigo_qr_id' => $qr->id, 'puerta_id' => $puertaId],
                        $pivot
                    );
                }
            }
        });

        if (!$qr) {
            return response()->json(['message' => 'No se pudo crear el QR.'], 500);
        }

        /** @var CodigoQr $qr */
        return response()->json([
            'message' => 'QR temporal creado (24h).',
            'data' => [
                'id' => $qr->id,
                'user_id' => $qr->user_id,
                'expires_at' => $expiresAt->toIso8601String(),
                'token' => $plainToken, // este es el valor a convertir a QR
            ],
        ], 201);
    }

    private function makeShortToken(int $len = 10): string
    {
        // Crockford-like base32 sin caracteres ambiguos: 2-9 y letras sin I, L, O.
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $max = strlen($alphabet) - 1;

        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }
}
