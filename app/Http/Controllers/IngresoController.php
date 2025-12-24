<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateCodigoQrRequest;
use App\Mail\QrCodeMail;
use App\Models\CodigoQr;
use App\Models\Puerta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class IngresoController extends Controller
{
    /**
     * Mostrar formulario de generación de QR
     */
    public function index(Request $request): Response
    {
        // Obtener usuarios activos para el selector
        $usuarios = User::query()
            ->where('activo', true)
            ->with(['role', 'cargo'])
            ->orderBy('name')
            ->get();

        // Obtener puertas activas para el selector (si se necesita)
        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Ingreso/Index', [
            'usuarios' => $usuarios,
            'puertas' => $puertas,
        ]);
    }

    /**
     * Generar QR y mostrar resultado
     */
    public function generate(GenerateCodigoQrRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();

        /** @var User $targetUser */
        $targetUser = User::query()->with('role')->findOrFail($data['user_id']);

        $actorRole = $actor?->role?->name;
        $targetRole = $targetUser->role?->name;

        // Validar permisos según rol
        if ($actorRole !== 'super_usuario') {
            if ($actorRole === 'operador' && $targetRole !== 'visitante') {
                return back()->withErrors(['user_id' => 'Operador solo puede generar QR para visitantes.']);
            }
            if ($actorRole === 'rrhh' && $targetRole === 'visitante') {
                return back()->withErrors(['user_id' => 'RRHH no puede generar QR para visitantes.']);
            }
            if ($actorRole === 'funcionario' && $actor?->id !== $targetUser->id) {
                return back()->withErrors(['user_id' => 'Funcionario solo puede generar su propio QR.']);
            }
        }

        $puertas = $data['puertas'] ?? [];
        $hasPuertas = is_array($puertas) && count($puertas) > 0;

        // Validar puertas para visitantes
        if ($targetRole === 'visitante' && !$hasPuertas) {
            return back()->withErrors(['puertas' => 'Para visitantes debes seleccionar al menos una puerta.']);
        }

        // Validar cargo para no visitantes
        if ($targetRole !== 'visitante' && !$hasPuertas && !$targetUser->cargo_id) {
            return back()->withErrors(['cargo_id' => 'El usuario no tiene cargo asignado. Asigna un cargo o selecciona puertas explícitas.']);
        }

        $now = Carbon::now();
        $expiresAt = $now->copy()->addHours(24);

        // Generar token único
        do {
            $plainToken = $this->makeShortToken(10);
            $tokenHash = hash('sha256', $plainToken);
        } while (CodigoQr::query()->where('codigo', $tokenHash)->exists());

        $qr = null;

        DB::transaction(function () use (&$qr, $targetUser, $actor, $tokenHash, $now, $expiresAt, $data) {
            $qr = CodigoQr::query()->create([
                'user_id' => $targetUser->id,
                'codigo' => $tokenHash,
                'fecha_generacion' => $now,
                'fecha_expiracion' => $expiresAt,
                'usado' => false,
                'activo' => true,
                'generado_por' => $actor?->id,
                'tipo' => 'temporal',
                'uso_actual' => 'pendiente',
                'intentos_fallidos' => 0,
            ]);

            // Asignar puertas si se enviaron
            $puertas = $data['puertas'] ?? [];
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
            return back()->withErrors(['error' => 'No se pudo crear el QR.']);
        }

        // Generar SVG del QR
        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($plainToken);

        // Asegurar que sea string (el método generate puede retornar un objeto)
        $qrSvg = strval($qrSvg);

        // Obtener usuarios y puertas para el formulario
        $usuarios = User::query()
            ->where('activo', true)
            ->with(['role', 'cargo'])
            ->orderBy('name')
            ->get();

        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Ingreso/Index', [
            'usuarios' => $usuarios,
            'puertas' => $puertas,
            'qrGenerado' => [
                'id' => $qr->id,
                'user_id' => $qr->user_id,
                'user_name' => $targetUser->name,
                'user_email' => $targetUser->email,
                'token' => $plainToken,
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_at_formatted' => $expiresAt->format('d/m/Y H:i'),
                'svg' => (string) $qrSvg, // Asegurar que sea string
            ],
        ]);
    }

    /**
     * Enviar QR por correo
     */
    public function sendEmail(Request $request, int $qrId)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'], // Token original del QR
        ]);

        $qr = CodigoQr::query()->with('user')->findOrFail($qrId);
        $user = $qr->user;

        // Verificar que el token coincida con el hash almacenado
        $tokenHash = hash('sha256', $request->token);
        if ($tokenHash !== $qr->codigo) {
            return back()->withErrors(['token' => 'Token inválido.']);
        }

        // Generar SVG del QR con el token original
        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($request->token);

        $qrSvg = strval($qrSvg);

        $expiresAt = Carbon::parse($qr->fecha_expiracion)->format('d/m/Y H:i');

        try {
            Mail::to($request->email)->send(new QrCodeMail(
                userName: $user->name ?? $user->email,
                qrToken: $request->token,
                qrSvg: $qrSvg,
                expiresAt: $expiresAt,
            ));

            return back()->with('message', 'QR enviado por correo exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error al enviar el correo: ' . $e->getMessage()]);
        }
    }

    /**
     * Descargar QR como imagen PNG
     */
    public function download(Request $request, int $qrId)
    {
        $request->validate([
            'token' => ['required', 'string'], // Token original del QR
        ]);

        $qr = CodigoQr::query()->with('user')->findOrFail($qrId);

        // Verificar que el token coincida con el hash almacenado
        $tokenHash = hash('sha256', $request->token);
        if ($tokenHash !== $qr->codigo) {
            abort(403, 'Token inválido.');
        }

        // Generar PNG del QR con el token original
        $qrPng = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->generate($request->token);

        $fileName = 'qr-' . ($qr->user->name ?? 'usuario') . '-' . $qr->id . '.png';
        $fileName = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $fileName);

        return response($qrPng, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function makeShortToken(int $len = 10): string
    {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $max = strlen($alphabet) - 1;

        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }
}
