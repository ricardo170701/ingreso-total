<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateCodigoQrRequest;
use App\Mail\QrCodeMail;
use App\Models\CodigoQr;
use App\Models\Secretaria;
use App\Models\Gerencia;
use App\Models\Piso;
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
use Illuminate\Support\Facades\Crypt;

class IngresoController extends Controller
{
    /**
     * Mostrar formulario de generación de QR
     */
    public function index(Request $request): Response
    {
        $actor = $request->user();

        // Si no tiene permiso para crear QR de otros, solo mostrar el usuario actual
        $esVisitante = ($actor?->role?->name ?? null) === 'visitante';
        $puedeCrearParaOtros = !$esVisitante && $actor && $actor->hasPermission('create_ingreso_otros');

        if ($puedeCrearParaOtros) {
            // Obtener todos los usuarios activos
            $usuarios = User::query()
                ->where('activo', true)
                ->with(['role', 'cargo'])
                ->orderBy('name')
                ->get();
        } else {
            // Solo mostrar el usuario actual
            $usuarios = collect([$actor])->filter();
        }

        // Obtener puertas activas para el selector
        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $secretarias = Secretaria::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'piso_id']);

        $gerencias = Gerencia::query()
            ->where('activo', true)
            ->with('secretaria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'secretaria_id']);

        // Si no tiene permiso para crear para otros, buscar QR activo del usuario actual
        $qrPersonal = null;
        if (!$puedeCrearParaOtros && $actor) {
            $qrPersonal = CodigoQr::query()
                ->where('user_id', $actor->id)
                ->activos()
                ->latest('fecha_generacion')
                ->first();
        }

        // Si tiene QR personal activo, prepararlo para mostrar
        $qrPersonalData = null;
        if ($qrPersonal) {
            // Obtener el token original desencriptado
            $tokenOriginal = $qrPersonal->token_original;

            // Generar SVG del QR si tenemos el token
            $qrSvg = null;
            if ($tokenOriginal) {
                $qrSvg = QrCode::format('svg')
                    ->size(300)
                    ->margin(2)
                    ->generate($tokenOriginal);
                $qrSvg = strval($qrSvg);
            }

            $actorRole = $actor->role?->name;
            $expiresAtFormatted = $qrPersonal->fecha_expiracion
                ? $qrPersonal->fecha_expiracion->format('d/m/Y H:i')
                : ($actorRole === 'funcionario' 
                    ? 'Hasta fin de contrato o inactivación' 
                    : 'No definido');

            $qrPersonalData = [
                'id' => $qrPersonal->id,
                'user_id' => $qrPersonal->user_id,
                'user_name' => $actor->name,
                'user_email' => $actor->email,
                'token' => $tokenOriginal,
                'expires_at' => $qrPersonal->fecha_expiracion?->toIso8601String(),
                'expires_at_formatted' => $expiresAtFormatted,
                'fecha_generacion' => $qrPersonal->fecha_generacion?->format('d/m/Y H:i'),
                'svg' => $qrSvg,
            ];
        }

        return Inertia::render('Ingreso/Index', [
            'usuarios' => $usuarios,
            'puertas' => $puertas,
            'pisos' => $pisos,
            'secretarias' => $secretarias,
            'gerencias' => $gerencias,
            'puedeCrearParaOtros' => $puedeCrearParaOtros,
            'qrPersonal' => $qrPersonalData,
        ]);
    }

    /**
     * Generar QR y mostrar resultado
     */
    public function generate(GenerateCodigoQrRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();

        // Visitante: no puede generar (solo ver/descargar su QR)
        if (($actor?->role?->name ?? null) === 'visitante') {
            abort(403, 'Como visitante solo puedes ver tu QR activo.');
        }

        /** @var User $targetUser */
        $targetUser = User::query()->with('role')->findOrFail($data['user_id']);

        // Validar permiso para crear QR de otros usuarios
        $puedeCrearParaOtros = $actor && $actor->hasPermission('create_ingreso_otros');

        // Si no tiene permiso para crear para otros, solo puede crear para sí mismo
        if (!$puedeCrearParaOtros && $actor && $actor->id !== $targetUser->id) {
            return back()->withErrors(['user_id' => 'No tienes permiso para generar QR para otros usuarios. Solo puedes generar tu propio QR.']);
        }

        $targetRole = $targetUser->role?->name;

        $puertas = $data['puertas'] ?? [];
        $pisos = $data['pisos'] ?? [];
        $hasPuertas = is_array($puertas) && count($puertas) > 0;
        $hasPisos = is_array($pisos) && count($pisos) > 0;

        // Validar pisos para visitantes (más práctico)
        if ($targetRole === 'visitante' && !$hasPisos) {
            return back()->withErrors(['pisos' => 'Para visitantes debes seleccionar al menos un piso.']);
        }

        if ($targetRole === 'visitante' && $hasPisos) {
            $puertasEnPisos = Puerta::query()
                ->where('activo', true)
                ->whereIn('piso_id', $pisos)
                ->count();
            if ($puertasEnPisos <= 0) {
                return back()->withErrors(['pisos' => 'Los pisos seleccionados no tienen puertas activas.']);
            }
        }

        // Para visitantes: registrar a qué gerencia va (solo cuando se genera para otros)
        if ($puedeCrearParaOtros && $targetRole === 'visitante' && empty($data['gerencia_id'])) {
            return back()->withErrors(['gerencia_id' => 'Para visitantes debes seleccionar la gerencia destino.']);
        }

        // Validar cargo para no visitantes
        if ($targetRole !== 'visitante' && !$hasPuertas && !$targetUser->cargo_id) {
            return back()->withErrors(['cargo_id' => 'El usuario no tiene cargo asignado. Asigna un cargo o selecciona puertas explícitas.']);
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

        // Generar token único
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
            $qr->gerencia_id = $data['gerencia_id'] ?? null;
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

            // Asignar puertas si se enviaron
            $puertas = $data['puertas'] ?? [];
            $pisos = $data['pisos'] ?? [];

            // Si es visitante: expandir pisos -> puertas activas
            if (($targetUser->role?->name ?? null) === 'visitante' && is_array($pisos) && count($pisos) > 0) {
                $puertas = Puerta::query()
                    ->where('activo', true)
                    ->whereIn('piso_id', $pisos)
                    ->pluck('id')
                    ->toArray();
            }

            if (is_array($puertas) && count($puertas) > 0) {
                // Para funcionarios: no guardar fechas ni horarios
                // El QR es válido hasta la fecha de expiración del usuario o hasta que esté inactivo
                if ($targetRole === 'funcionario') {
                    $pivot = [
                        'hora_inicio' => null,
                        'hora_fin' => null,
                        'dias_semana' => '1,2,3,4,5,6,7',
                        'fecha_inicio' => null,
                        'fecha_fin' => null,
                        'activo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Para visitantes: usar las fechas y horarios proporcionados
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
                }

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
        $puedeCrearParaOtros = $actor && $actor->hasPermission('create_ingreso_otros');

        if ($puedeCrearParaOtros) {
            $usuarios = User::query()
                ->where('activo', true)
                ->with(['role', 'cargo'])
                ->orderBy('name')
                ->get();
        } else {
            $usuarios = collect([$actor])->filter();
        }

        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $secretarias = Secretaria::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'piso_id']);

        $gerencias = Gerencia::query()
            ->where('activo', true)
            ->with('secretaria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'secretaria_id']);

        return Inertia::render('Ingreso/Index', [
            'usuarios' => $usuarios,
            'puertas' => $puertas,
            'pisos' => $pisos,
            'secretarias' => $secretarias,
            'gerencias' => $gerencias,
            'puedeCrearParaOtros' => $puedeCrearParaOtros,
            'qrGenerado' => [
                'id' => $qr->id,
                'user_id' => $qr->user_id,
                'user_name' => $targetUser->name,
                'user_email' => $targetUser->email,
                'token' => $plainToken,
                'expires_at' => $expiresAt?->toIso8601String(),
                'expires_at_formatted' => $expiresAt 
                    ? $expiresAt->format('d/m/Y H:i')
                    : ($targetRole === 'funcionario' 
                        ? 'Hasta fin de contrato o inactivación' 
                        : 'No definido'),
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

        $expiresAt = $qr->fecha_expiracion
            ? Carbon::parse($qr->fecha_expiracion)->format('d/m/Y H:i')
            : null;

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
