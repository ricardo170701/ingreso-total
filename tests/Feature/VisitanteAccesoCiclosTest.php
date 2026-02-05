<?php

namespace Tests\Feature;

use App\Models\Acceso;
use App\Models\Cargo;
use App\Models\CodigoQr;
use App\Models\Permission;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VisitanteAccesoCiclosTest extends TestCase
{
    use DatabaseTransactions;

    private const LOG_PATH = 'logs/visitante-acceso-ciclos-test.log';

    private function setAccessDeviceKey(string $key): void
    {
        putenv('ACCESS_DEVICE_KEY=' . $key);
        $_ENV['ACCESS_DEVICE_KEY'] = $key;
        $_SERVER['ACCESS_DEVICE_KEY'] = $key;
    }

    private function resetDebugLog(): void
    {
        @file_put_contents(storage_path(self::LOG_PATH), '');
    }

    private function debugLog(string $event, array $context = []): void
    {
        $payload = array_merge([
            'ts' => now()->toIso8601String(),
            'event' => $event,
        ], $context);

        @file_put_contents(
            storage_path(self::LOG_PATH),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }

    private function lastAccesoForQr(int $codigoQrId): ?Acceso
    {
        return Acceso::query()
            ->where('codigo_qr_id', $codigoQrId)
            ->orderByDesc('fecha_acceso')
            ->orderByDesc('id')
            ->first();
    }

    private function seedActorConPermisos(array $permissionNames): User
    {
        $roleStaff = Role::query()->firstOrCreate(
            ['name' => 'servidor_publico'],
            ['description' => 'Servidor público']
        );

        $cargo = Cargo::query()->firstOrCreate(
            ['name' => 'Operador Ingreso (tests)'],
            ['description' => 'Cargo para pruebas', 'activo' => true]
        );

        $permIds = [];
        foreach ($permissionNames as $name) {
            $perm = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['description' => null, 'group' => 'tests', 'activo' => true]
            );
            $permIds[] = $perm->id;
        }
        $cargo->permissions()->syncWithoutDetaching($permIds);

        return User::factory()->create([
            'email' => 'actor+' . uniqid() . '@local.test',
            'password' => Hash::make('secret12345'),
            // Pasar middleware force.password.change si aplica
            'password_changed_at' => now(),
            'activo' => true,
            'role_id' => $roleStaff->id,
            'cargo_id' => $cargo->id,
        ]);
    }

    private function makeVisitanteConCorreo(): User
    {
        $roleVisitante = Role::query()->firstOrCreate(
            ['name' => 'visitante'],
            ['description' => 'Visitante']
        );

        return User::factory()->create([
            'email' => 'visitante+' . uniqid() . '@local.test',
            'activo' => true,
            'role_id' => $roleVisitante->id,
            'cargo_id' => null,
        ]);
    }

    public function test_visitante_qr_con_permisos_piso1_soporta_15_ciclos_en_diferentes_puertas(): void
    {
        $this->setAccessDeviceKey('1234abcd');
        $this->resetDebugLog();
        $this->debugLog('test_start', [
            'test' => __METHOD__,
            'access_device_key' => '1234abcd',
        ]);

        // OJO: la ruta `api` suele traer `throttle:api` (por defecto 60 req/min).
        // Para debug de lógica de acceso (no rate limit), deshabilitamos throttle a menos que el usuario lo fuerce.
        $keepThrottle = in_array(strtolower((string) getenv('TEST_KEEP_THROTTLE')), ['1', 'true', 'yes'], true);
        if (!$keepThrottle) {
            $this->withoutMiddleware(ThrottleRequests::class);
        }
        $this->debugLog('throttle', [
            'kept' => $keepThrottle,
        ]);

        $actor = $this->seedActorConPermisos(['create_ingreso', 'create_ingreso_otros']);
        $visitante = $this->makeVisitanteConCorreo();

        $this->debugLog('users_created', [
            'actor_id' => $actor->id,
            'visitante_id' => $visitante->id,
        ]);

        $piso1 = Piso::query()->create([
            'nombre' => 'Piso 1 (tests)',
            'orden' => 1,
            'activo' => true,
        ]);

        $puertas = collect(range(1, 3))->map(function (int $i) use ($piso1) {
            return Puerta::query()->create([
                'nombre' => 'Puerta Piso 1 - ' . $i . ' (tests)',
                'piso_id' => $piso1->id,
                'activo' => true,
                'solo_servidores_publicos' => false,
                'requiere_permiso_datacenter' => false,
                'requiere_discapacidad' => false,
                'codigo_fisico' => 'P1-D' . $i . '-ENT-' . uniqid(),
                'codigo_fisico_salida' => 'P1-D' . $i . '-SAL-' . uniqid(),
            ]);
        })->values();

        $this->debugLog('fixtures_created', [
            'piso_id' => $piso1->id,
            'puertas' => $puertas->map(fn(Puerta $p) => [
                'id' => $p->id,
                'codigo_fisico' => $p->codigo_fisico,
                'codigo_fisico_salida' => $p->codigo_fisico_salida,
            ])->toArray(),
        ]);

        Sanctum::actingAs($actor);

        $qrRes = $this->postJson('/api/qrs', [
            'user_id' => $visitante->id,
            'pisos' => [$piso1->id],
        ]);

        // Loguear SIEMPRE (aunque sea 500) para diagnóstico en producción
        $this->debugLog('qr_created_response', [
            'status' => $qrRes->status(),
            'json' => $qrRes->json(),
            'content' => method_exists($qrRes, 'getContent') ? $qrRes->getContent() : null,
        ]);

        $qrRes->assertStatus(201);
        $plainToken = $qrRes->json('data.token');
        $this->assertIsString($plainToken);
        $this->assertNotEmpty($plainToken);

        $qr = CodigoQr::query()
            ->where('user_id', $visitante->id)
            ->where('activo', true)
            ->orderByDesc('id')
            ->firstOrFail();

        $this->debugLog('qr_db', [
            'codigo_qr_id' => $qr->id,
            'user_id' => $qr->user_id,
            'activo' => $qr->activo,
            'fecha_generacion' => optional($qr->fecha_generacion)->toIso8601String(),
            'fecha_expiracion' => optional($qr->fecha_expiracion)->toIso8601String(),
        ]);

        foreach ($puertas as $puerta) {
            $this->assertDatabaseHas('codigo_qr_puerta_acceso', [
                'codigo_qr_id' => $qr->id,
                'puerta_id' => $puerta->id,
                'activo' => 1,
            ]);
        }

        // 15 ciclos: en cada ciclo forzamos 4 llamadas:
        // - entrada OK
        // - entrada repetida (DEBE fallar: ya hay entrada activa)
        // - salida OK
        // - salida repetida (DEBE fallar: ya no hay entrada activa)
        $headers = [
            'X-DEVICE-KEY' => '1234abcd',
            'accept' => '*/*',
        ];

        $codigoQrIdFromVerify = null;

        for ($ciclo = 1; $ciclo <= 15; $ciclo++) {
            /** @var Puerta $puerta */
            $puerta = $puertas[($ciclo - 1) % $puertas->count()];

            $entradaPayload = [
                'token' => $plainToken,
                'codigo_fisico' => $puerta->codigo_fisico,
                'tipo_evento' => 'entrada',
                'dispositivo_id' => 'P1-D' . $puerta->id . '-RPI-ENTRADA',
            ];
            $this->debugLog('verify_request', [
                'ciclo' => $ciclo,
                'fase' => 'entrada',
                'headers' => $headers,
                'payload' => $entradaPayload,
            ]);
            $entradaRes = $this->withHeaders($headers)->postJson('/api/access/verify', $entradaPayload);
            if ($entradaRes->status() === 429) {
                $this->debugLog('throttle_429', [
                    'ciclo' => $ciclo,
                    'fase' => 'entrada',
                    'status' => $entradaRes->status(),
                    'json' => $entradaRes->json(),
                ]);
                $this->fail('Recibido 429 (throttle) en entrada. Revisa rate limit (throttle:api) o ejecuta con TEST_KEEP_THROTTLE=1 para reproducir.');
            }

            $entradaRes->assertOk();
            $entradaRes->assertJsonPath('permitido', true);
            $entradaRes->assertJsonPath('data.tipo_evento', 'entrada');

            $codigoQrIdFromVerify = $codigoQrIdFromVerify ?? $entradaRes->json('data.codigo_qr_id');

            $this->debugLog('verify_response', [
                'ciclo' => $ciclo,
                'fase' => 'entrada',
                'status' => $entradaRes->status(),
                'json' => $entradaRes->json(),
                'last_acceso' => ($codigoQrIdFromVerify ? optional($this->lastAccesoForQr((int) $codigoQrIdFromVerify))->toArray() : null),
            ]);

            // Intento explícito: doble entrada (debe fallar)
            $entrada2Payload = $entradaPayload;
            $entrada2Payload['dispositivo_id'] = 'P1-D' . $puerta->id . '-RPI-ENTRADA-RETRY';
            $this->debugLog('verify_request', [
                'ciclo' => $ciclo,
                'fase' => 'entrada_repeat',
                'headers' => $headers,
                'payload' => $entrada2Payload,
            ]);
            $entrada2Res = $this->withHeaders($headers)->postJson('/api/access/verify', $entrada2Payload);
            if ($entrada2Res->status() === 429) {
                $this->debugLog('throttle_429', [
                    'ciclo' => $ciclo,
                    'fase' => 'entrada_repeat',
                    'status' => $entrada2Res->status(),
                    'json' => $entrada2Res->json(),
                ]);
                $this->fail('Recibido 429 (throttle) en entrada_repeat. Esto puede explicar fallos en producción si el lector hace ráfagas.');
            }
            $entrada2Res->assertOk();
            $entrada2Res->assertJsonPath('permitido', false);
            $this->assertStringContainsString('Entrada ya registrada', (string) $entrada2Res->json('message'));
            $this->debugLog('verify_response', [
                'ciclo' => $ciclo,
                'fase' => 'entrada_repeat',
                'status' => $entrada2Res->status(),
                'json' => $entrada2Res->json(),
                'last_acceso' => ($codigoQrIdFromVerify ? optional($this->lastAccesoForQr((int) $codigoQrIdFromVerify))->toArray() : null),
            ]);

            if ($codigoQrIdFromVerify) {
                $last = $this->lastAccesoForQr((int) $codigoQrIdFromVerify);
                $this->assertNotNull($last);
                $this->assertFalse((bool) $last->permitido);
                $this->assertEquals('denegado', $last->tipo_evento);
            }

            $salidaPayload = [
                'token' => $plainToken,
                'codigo_fisico' => $puerta->codigo_fisico_salida,
                'tipo_evento' => 'salida',
                'dispositivo_id' => 'P1-D' . $puerta->id . '-RPI-SALIDA',
            ];
            $this->debugLog('verify_request', [
                'ciclo' => $ciclo,
                'fase' => 'salida',
                'headers' => $headers,
                'payload' => $salidaPayload,
            ]);
            $salidaRes = $this->withHeaders($headers)->postJson('/api/access/verify', $salidaPayload);
            if ($salidaRes->status() === 429) {
                $this->debugLog('throttle_429', [
                    'ciclo' => $ciclo,
                    'fase' => 'salida',
                    'status' => $salidaRes->status(),
                    'json' => $salidaRes->json(),
                ]);
                $this->fail('Recibido 429 (throttle) en salida. Esto puede explicar fallos en producción si el lector reintenta rápido.');
            }

            $salidaRes->assertOk();
            $salidaRes->assertJsonPath('permitido', true);
            $salidaRes->assertJsonPath('data.tipo_evento', 'salida');

            $this->debugLog('verify_response', [
                'ciclo' => $ciclo,
                'fase' => 'salida',
                'status' => $salidaRes->status(),
                'json' => $salidaRes->json(),
                'last_acceso' => ($codigoQrIdFromVerify ? optional($this->lastAccesoForQr((int) $codigoQrIdFromVerify))->toArray() : null),
            ]);

            // Intento explícito: doble salida (debe fallar)
            $salida2Payload = $salidaPayload;
            $salida2Payload['dispositivo_id'] = 'P1-D' . $puerta->id . '-RPI-SALIDA-RETRY';
            $this->debugLog('verify_request', [
                'ciclo' => $ciclo,
                'fase' => 'salida_repeat',
                'headers' => $headers,
                'payload' => $salida2Payload,
            ]);
            $salida2Res = $this->withHeaders($headers)->postJson('/api/access/verify', $salida2Payload);
            if ($salida2Res->status() === 429) {
                $this->debugLog('throttle_429', [
                    'ciclo' => $ciclo,
                    'fase' => 'salida_repeat',
                    'status' => $salida2Res->status(),
                    'json' => $salida2Res->json(),
                ]);
                $this->fail('Recibido 429 (throttle) en salida_repeat. Esto es un indicio fuerte de rate limit como causa del fallo.');
            }
            $salida2Res->assertOk();
            $salida2Res->assertJsonPath('permitido', false);
            $this->assertStringContainsString('No hay una entrada activa', (string) $salida2Res->json('message'));
            $this->debugLog('verify_response', [
                'ciclo' => $ciclo,
                'fase' => 'salida_repeat',
                'status' => $salida2Res->status(),
                'json' => $salida2Res->json(),
                'last_acceso' => ($codigoQrIdFromVerify ? optional($this->lastAccesoForQr((int) $codigoQrIdFromVerify))->toArray() : null),
            ]);

            if ($codigoQrIdFromVerify) {
                $last = $this->lastAccesoForQr((int) $codigoQrIdFromVerify);
                $this->assertNotNull($last);
                $this->assertFalse((bool) $last->permitido);
                $this->assertEquals('denegado', $last->tipo_evento);
            }
        }

        $this->assertEquals($qr->id, $codigoQrIdFromVerify);

        $okCount = Acceso::query()
            ->where('codigo_qr_id', $qr->id)
            ->where('permitido', true)
            ->whereIn('tipo_evento', ['entrada', 'salida'])
            ->count();

        // 15 ciclos * 2 OK (entrada+salida) = 30
        $this->assertEquals(30, $okCount);

        $denegados = Acceso::query()
            ->where('codigo_qr_id', $qr->id)
            ->where('permitido', false)
            ->count();

        // 15 ciclos * 2 denegados (entrada repeat + salida repeat) = 30
        $this->assertEquals(30, $denegados);

        // Bonus: asegurar que no quedaron "entradas" colgadas (último permitido debe ser salida)
        $lastOk = Acceso::query()
            ->where('codigo_qr_id', $qr->id)
            ->where('permitido', true)
            ->orderByDesc('fecha_acceso')
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($lastOk);
        $this->assertEquals('salida', $lastOk->tipo_evento);

        $this->debugLog('test_end', [
            'codigo_qr_id' => $qr->id,
            'ok_count' => $okCount,
            'denegados' => $denegados,
            'last_ok' => optional($lastOk)->toArray(),
            'log_file' => storage_path(self::LOG_PATH),
        ]);
    }
}
