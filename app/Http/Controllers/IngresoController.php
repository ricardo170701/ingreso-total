<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateCodigoQrRequest;
use App\Mail\QrCodeMail;
use App\Models\CodigoQr;
use App\Models\Secretaria;
use App\Models\Gerencia;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\Role;
use App\Models\TarjetaNfc;
use App\Models\TarjetaNfcAsignacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

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
        $puedeVerFuncionariosEnIngreso = !$esVisitante && $actor && $actor->hasPermission('view_ingreso_funcionarios');

        if ($puedeCrearParaOtros) {
            // Por defecto, en Ingreso se listan solo visitantes.
            // Solo si tiene permiso extra, se listan también servidores públicos/contratistas.
            $usuariosQ = User::query()
                ->where('activo', true)
                ->with(['role', 'cargo'])
                ->orderBy('name');

            if (!$puedeVerFuncionariosEnIngreso) {
                $usuariosQ->whereHas('role', fn($r) => $r->where('name', 'visitante'));
            }

            $usuarios = $usuariosQ->get();
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

        // Obtener usuarios servidores públicos y contratistas para selector de responsable
        $responsables = User::query()
            ->where('activo', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['servidor_publico', 'contratista', 'funcionario']); // 'funcionario' legado
            })
            ->with(['role', 'cargo'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id', 'cargo_id']);

        // Buscar QR activo del usuario actual (si existe)
        $qrPersonal = null;
        if ($actor) {
            $actorRole = $actor->role?->name;
            $staffRoles = ['servidor_publico', 'contratista', 'funcionario']; // 'funcionario' legado
            $isStaff = in_array($actorRole, $staffRoles, true);

            // IMPORTANTE:
            // - Para visitantes: el QR expira por codigos_qr.fecha_expiracion
            // - Para staff: el QR NO debe depender de codigos_qr.fecha_expiracion (depende de users.fecha_expiracion)
            // Por eso NO usamos el scope activos() aquí para staff.
            $qrQ = CodigoQr::query()
                ->where('user_id', $actor->id)
                ->where('activo', true)
                ->latest('fecha_generacion');

            if (!$isStaff) {
                $now = now();
                $qrQ->where(function ($q) use ($now) {
                    $q->whereNull('fecha_expiracion')
                        ->orWhere('fecha_expiracion', '>', $now);
                });
            }

            $qrPersonal = $qrQ->first();
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
            $staffRoles = ['servidor_publico', 'contratista', 'funcionario']; // 'funcionario' legado
            $isStaff = in_array($actorRole, $staffRoles, true);

            // Mostrar expiración:
            // - Staff: SIEMPRE depende de users.fecha_expiracion
            // - Visitante: depende de codigos_qr.fecha_expiracion
            $expiresAtIso = null;
            $expiresAtFormatted = null;
            if ($isStaff) {
                if ($actor->fecha_expiracion) {
                    $expires = Carbon::parse($actor->fecha_expiracion)->endOfDay();
                    $expiresAtIso = $expires->toIso8601String();
                    $expiresAtFormatted = $expires->format('d/m/Y H:i');
                } else {
                    $expiresAtIso = null;
                    $expiresAtFormatted = 'Hasta fin de contrato o inactivación';
                }
            } else {
                $expiresAtIso = $qrPersonal->fecha_expiracion?->toIso8601String();
                $expiresAtFormatted = $qrPersonal->fecha_expiracion
                    ? $qrPersonal->fecha_expiracion->format('d/m/Y H:i')
                    : 'No definido';
            }

            $qrPersonalData = [
                'id' => $qrPersonal->id,
                'user_id' => $qrPersonal->user_id,
                'user_name' => $actor->name,
                'user_email' => $actor->email,
                'token' => $tokenOriginal,
                'expires_at' => $expiresAtIso,
                'expires_at_formatted' => $expiresAtFormatted,
                'fecha_generacion' => $qrPersonal->fecha_generacion?->format('d/m/Y H:i'),
                'svg' => $qrSvg,
            ];
        }

        // Tarjetas NFC disponibles para asignar desde Ingreso
        // Solo mostrar tarjetas activas que NO estén asignadas (user_id IS NULL)
        $tarjetasNfcDisponibles = TarjetaNfc::query()
            ->where('activo', true)
            ->whereNull('user_id')
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);

        // Agregar información de tarjeta NFC asignada a cada usuario (si tiene)
        $usuarios = $usuarios->map(function ($usuario) {
            $tarjetaAsignada = TarjetaNfc::query()
                ->where('activo', true)
                ->where('user_id', $usuario->id)
                ->first(['id', 'codigo', 'nombre']);

            $usuario->tarjeta_nfc_asignada = $tarjetaAsignada ? [
                'id' => $tarjetaAsignada->id,
                'codigo' => $tarjetaAsignada->codigo,
                'nombre' => $tarjetaAsignada->nombre,
            ] : null;

            return $usuario;
        });

        return Inertia::render('Ingreso/Index', [
            'usuarios' => $usuarios,
            'puertas' => $puertas,
            'pisos' => $pisos,
            'secretarias' => $secretarias,
            'gerencias' => $gerencias,
            'responsables' => $responsables->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'cargo' => $u->cargo ? ['id' => $u->cargo->id, 'name' => $u->cargo->name] : null,
            ]),
            'puedeCrearParaOtros' => $puedeCrearParaOtros,
            'qrPersonal' => $qrPersonalData,
            'tarjetasNfcDisponibles' => $tarjetasNfcDisponibles,
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

        // Nuevo tipo: visitante sin correo -> no se genera QR (solo tarjeta NFC)
        if ($targetRole === 'visitante' && empty($targetUser->email)) {
            return back()->withErrors([
                'user_id' => 'Este visitante no tiene correo electrónico. No requiere QR: solo se puede asignar tarjeta NFC.',
            ]);
        }

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

        // Para visitantes: si no se envía gerencia, se registra como "Despacho" (null)

        // Validar cargo para no visitantes
        if ($targetRole !== 'visitante' && !$hasPuertas && !$targetUser->cargo_id) {
            return back()->withErrors(['cargo_id' => 'El usuario no tiene cargo asignado. Asigna un cargo o selecciona puertas explícitas.']);
        }

        $now = Carbon::now();

        // Para staff (servidor público/contratista):
        // - Si tiene fecha_expiracion: usar esa fecha
        // - Si NO tiene fecha_expiracion (contrato indefinido): null (el acceso se controla solo por campo 'activo')
        // Para visitantes: si envía fecha_fin (y opcional hora_fin), usarla como expiración; si no, mantener 15 días
        $staffRoles = ['servidor_publico', 'contratista', 'funcionario']; // 'funcionario' legado
        $isStaff = in_array($targetRole, $staffRoles, true);
        if ($isStaff) {
            if ($targetUser->fecha_expiracion) {
                $expiresAt = Carbon::parse($targetUser->fecha_expiracion)->endOfDay();
            } else {
                // Contrato indefinido: el QR no expira, el acceso se controla solo por el campo 'activo' del usuario
                $expiresAt = null;
            }
        } else {
            $fechaFin = $data['fecha_fin'] ?? $data['fecha_inicio'] ?? null;
            $horaFin = $data['hora_fin'] ?? null;
            if ($fechaFin) {
                if ($horaFin) {
                    $expiresAt = Carbon::createFromFormat('Y-m-d H:i', $fechaFin . ' ' . $horaFin)->setSecond(59);
                } else {
                    $expiresAt = Carbon::parse($fechaFin)->endOfDay();
                }
            } else {
                $expiresAt = $now->copy()->addDays(15);
            }
        }

        // Generar token único
        do {
            $plainToken = $this->makeShortToken(10);
            $tokenHash = hash('sha256', $plainToken);
        } while (CodigoQr::query()->where('codigo', $tokenHash)->exists());

        $qr = null;

        DB::transaction(function () use (&$qr, $targetUser, $actor, $tokenHash, $now, $expiresAt, $data, $plainToken, $targetRole) {
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
            $qr->responsable_id = $data['responsable_id'] ?? null;
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
                // Para staff (servidor público/contratista): no guardar fechas ni horarios
                // El QR es válido hasta la fecha de expiración del usuario o hasta que esté inactivo
                $staffRoles = ['servidor_publico', 'contratista', 'funcionario']; // 'funcionario' legado
                $isStaff = in_array($targetRole, $staffRoles, true);
                if ($isStaff) {
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
        $esVisitanteActor = ($actor?->role?->name ?? null) === 'visitante';
        $puedeVerFuncionariosEnIngreso = !$esVisitanteActor && $actor && $actor->hasPermission('view_ingreso_funcionarios');

        if ($puedeCrearParaOtros) {
            $usuariosQ = User::query()
                ->where('activo', true)
                ->with(['role', 'cargo'])
                ->orderBy('name');

            if (!$puedeVerFuncionariosEnIngreso) {
                $usuariosQ->whereHas('role', fn($r) => $r->where('name', 'visitante'));
            }

            $usuarios = $usuariosQ->get();
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

        // Obtener usuarios servidores públicos y contratistas para selector de responsable
        $responsables = User::query()
            ->where('activo', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['servidor_publico', 'contratista', 'funcionario']); // 'funcionario' legado
            })
            ->with(['role', 'cargo'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id', 'cargo_id']);

        return Inertia::render('Ingreso/Index', [
            'usuarios' => $usuarios,
            'puertas' => $puertas,
            'pisos' => $pisos,
            'secretarias' => $secretarias,
            'gerencias' => $gerencias,
            'responsables' => $responsables->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'cargo' => $u->cargo ? ['id' => $u->cargo->id, 'name' => $u->cargo->name] : null,
            ]),
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
                    : (in_array($targetRole, ['servidor_publico', 'contratista', 'funcionario'], true)
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
     * Crear un usuario visitante desde la pantalla de Ingreso.
     * Requiere permiso: create_ingreso_visitantes
     */
    public function storeVisitante(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (!$actor) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // Normalizar email: "" -> null
        if (is_string($request->input('email')) && trim((string) $request->input('email')) === '') {
            $request->merge(['email' => null]);
        }

        // Visitante no puede crear visitantes
        if (($actor?->role?->name ?? null) === 'visitante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (!$actor->hasPermission('create_ingreso_visitantes')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            // Email opcional: si no se proporciona, el visitante NO podrá iniciar sesión ni generar QR.
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'n_identidad' => ['required', 'string', 'max:50', 'unique:users,n_identidad'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'file', 'image', 'max:4096'],
        ]);

        $roleVisitanteId = Role::query()->where('name', 'visitante')->value('id');
        if (!$roleVisitanteId) {
            return response()->json(['message' => 'No existe el rol visitante.'], 500);
        }

        $nombre = trim((string) ($data['nombre'] ?? ''));
        $apellido = trim((string) ($data['apellido'] ?? ''));
        $name = trim($nombre . ' ' . $apellido) ?: 'Visitante';

        // Password aleatorio (no se entrega; el visitante no necesita login para QR)
        $randomPassword = Str::random(16);

        // Guardar foto (archivo) si viene
        $fotoPerfilPath = null;
        if ($request->hasFile('foto')) {
            $fotoPerfilPath = $request->file('foto')->store('fotos_perfil', 'public');
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $data['email'] ?? null,
            'password' => $randomPassword,
            'role_id' => $roleVisitanteId,
            'cargo_id' => null,
            'gerencia_id' => null,
            'fecha_expiracion' => null,
            'activo' => true,
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'n_identidad' => $data['n_identidad'] ?? null,
            'numero_caso' => $data['numero_caso'] ?? null,
            'foto_perfil' => $fotoPerfilPath,
            // Auditoría (legacy + nuevo)
            'creado_por' => $actor->id,
            'created_by' => $actor->id,
        ]);

        return response()->json([
            'message' => 'Visitante creado correctamente.',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'activo' => (bool) $user->activo,
                'foto_perfil' => $user->foto_perfil,
                'n_identidad' => $user->n_identidad,
                'observaciones' => $user->observaciones,
                'role' => ['id' => $roleVisitanteId, 'name' => 'visitante'],
                'cargo' => null,
            ],
        ], 201);
    }

    /**
     * Asignar una tarjeta NFC a un visitante desde Ingreso.
     * Requiere permiso: asignar_tarjetas_nfc
     */
    public function asignarTarjetaNfc(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (!$actor) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$actor->hasPermission('asignar_tarjetas_nfc')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'tarjeta_nfc_id' => ['required', 'integer', 'exists:tarjetas_nfc,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'gerencia_id' => ['nullable', 'integer', 'exists:gerencias,id'],
            'pisos' => ['required', 'array', 'min:1'],
            'pisos.*' => ['integer', 'exists:pisos,id'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'dias_semana' => ['nullable', 'string', 'max:20'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $tarjeta = TarjetaNfc::query()->findOrFail($data['tarjeta_nfc_id']);
        $targetUser = User::query()->with('role')->findOrFail($data['user_id']);

        // Solo se pueden asignar tarjetas a visitantes
        if (($targetUser->role?->name ?? null) !== 'visitante') {
            return response()->json(['message' => 'Las tarjetas NFC solo se pueden asignar a visitantes.'], 422);
        }

        // Validar que la tarjeta no esté asignada a otro usuario
        if ($tarjeta->user_id && $tarjeta->user_id !== $targetUser->id) {
            return response()->json(['message' => 'La tarjeta ya está asignada a otro usuario.'], 422);
        }

        $now = Carbon::now();
        $expiresAt = $now->copy()->addDays(15); // Similar a QR de visitantes

        DB::transaction(function () use ($tarjeta, $targetUser, $actor, $data, $expiresAt, $now) {
            // Cerrar asignación anterior (si existe)
            TarjetaNfcAsignacion::query()
                ->where('tarjeta_nfc_id', $tarjeta->id)
                ->whereNull('fecha_desasignacion')
                ->update([
                    'fecha_desasignacion' => $now,
                    'updated_at' => now(),
                ]);

            // Asignar tarjeta al usuario
            $tarjeta->update([
                'user_id' => $targetUser->id,
                'gerencia_id' => $data['gerencia_id'] ?? null,
                'fecha_asignacion' => $now,
                'fecha_expiracion' => $expiresAt,
                'activo' => true,
                'asignado_por' => $actor->id,
            ]);

            // Registrar historial
            TarjetaNfcAsignacion::query()->create([
                'tarjeta_nfc_id' => $tarjeta->id,
                'user_id' => $targetUser->id,
                'asignado_por' => $actor->id,
                'gerencia_id' => $data['gerencia_id'] ?? null,
                'fecha_asignacion' => $now,
                'fecha_desasignacion' => null,
            ]);

            // Expandir pisos a puertas activas
            $puertas = Puerta::query()
                ->where('activo', true)
                ->whereIn('piso_id', $data['pisos'])
                ->pluck('id')
                ->toArray();

            // Eliminar relaciones anteriores
            DB::table('tarjeta_nfc_puerta_acceso')
                ->where('tarjeta_nfc_id', $tarjeta->id)
                ->delete();

            // Crear nuevas relaciones
            if (count($puertas) > 0) {
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
                    DB::table('tarjeta_nfc_puerta_acceso')->insert([
                        'tarjeta_nfc_id' => $tarjeta->id,
                        'puerta_id' => $puertaId,
                        ...$pivot,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Tarjeta NFC asignada correctamente.',
            'data' => [
                'id' => $tarjeta->id,
                'codigo' => $tarjeta->codigo,
                'nombre' => $tarjeta->nombre,
                'user_id' => $tarjeta->user_id,
            ],
        ], 200);
    }

    /**
     * Desasignar una tarjeta NFC (dejarla disponible) desde Ingreso.
     * Requiere permiso: asignar_tarjetas_nfc
     */
    public function desasignarTarjetaNfc(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (!$actor) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$actor->hasPermission('asignar_tarjetas_nfc')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'tarjeta_nfc_id' => ['required', 'integer', 'exists:tarjetas_nfc,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $tarjeta = TarjetaNfc::query()->findOrFail($data['tarjeta_nfc_id']);

        if (!$tarjeta->user_id) {
            return response()->json(['message' => 'La tarjeta no está asignada.'], 422);
        }

        if ((int) $tarjeta->user_id !== (int) $data['user_id']) {
            return response()->json(['message' => 'La tarjeta no está asignada a este usuario.'], 422);
        }

        $now = Carbon::now();

        DB::transaction(function () use ($tarjeta, $now) {
            // Cerrar asignación abierta (si existe)
            TarjetaNfcAsignacion::query()
                ->where('tarjeta_nfc_id', $tarjeta->id)
                ->whereNull('fecha_desasignacion')
                ->update([
                    'fecha_desasignacion' => $now,
                    'updated_at' => now(),
                ]);

            // Desasignar tarjeta
            $tarjeta->update([
                'user_id' => null,
                'gerencia_id' => null,
                'fecha_asignacion' => null,
                'fecha_expiracion' => null,
                'asignado_por' => null,
            ]);
        });

        return response()->json([
            'message' => 'Tarjeta NFC desasignada correctamente.',
            'data' => [
                'id' => $tarjeta->id,
                'codigo' => $tarjeta->codigo,
                'nombre' => $tarjeta->nombre,
                'user_id' => $tarjeta->user_id,
            ],
        ], 200);
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
