<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTarjetaNfcRequest;
use App\Http\Requests\UpdateTarjetaNfcRequest;
use App\Models\Gerencia;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\Secretaria;
use App\Models\TarjetaNfc;
use App\Models\TarjetaNfcAsignacion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TarjetasNfcController extends Controller
{
    /**
     * Listar tarjetas NFC
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TarjetaNfc::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));
        $search = trim((string) $request->query('search', ''));

        $query = TarjetaNfc::query()
            ->with(['user.role', 'gerencia.secretaria.piso', 'asignadoPor']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', '%' . $search . '%')
                    ->orWhere('nombre', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('n_identidad', 'like', '%' . $search . '%'));
            });
        }

        $tarjetas = $query->orderByDesc('id')->paginate($perPage)->withQueryString()
            ->through(fn(TarjetaNfc $t) => [
                'id' => $t->id,
                'codigo' => $t->codigo,
                'nombre' => $t->nombre,
                'user' => $t->user ? [
                    'id' => $t->user->id,
                    'name' => $t->user->name,
                    'email' => $t->user->email,
                    'n_identidad' => $t->user->n_identidad,
                    'role' => $t->user->role ? ['id' => $t->user->role->id, 'name' => $t->user->role->name] : null,
                ] : null,
                'gerencia' => $t->gerencia ? [
                    'id' => $t->gerencia->id,
                    'nombre' => $t->gerencia->nombre,
                    'secretaria' => $t->gerencia->secretaria ? [
                        'id' => $t->gerencia->secretaria->id,
                        'nombre' => $t->gerencia->secretaria->nombre,
                    ] : null,
                ] : null,
                'fecha_asignacion' => $t->fecha_asignacion?->format('d/m/Y H:i'),
                'fecha_expiracion' => $t->fecha_expiracion?->format('d/m/Y H:i'),
                'activo' => (bool) $t->activo,
                'asignado_por' => $t->asignadoPor ? [
                    'id' => $t->asignadoPor->id,
                    'name' => $t->asignadoPor->name,
                ] : null,
                'created_at' => $t->created_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('TarjetasNfc/Index', [
            'tarjetas' => $tarjetas,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $this->authorize('create', TarjetaNfc::class);

        $gerencias = Gerencia::query()
            ->where('activo', true)
            ->with('secretaria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'secretaria_id']);

        $secretarias = Secretaria::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'piso_id']);

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        // Lista de usuarios (funcionarios y visitantes) para asignar tarjetas
        $usuarios = User::query()
            ->where('activo', true)
            ->with('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'n_identidad', 'role_id']);

        return Inertia::render('TarjetasNfc/Create', [
            'gerencias' => $gerencias,
            'secretarias' => $secretarias,
            'pisos' => $pisos,
            'puertas' => $puertas,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Guardar nueva tarjeta NFC
     */
    public function store(StoreTarjetaNfcRequest $request): RedirectResponse
    {
        $this->authorize('create', TarjetaNfc::class);

        $data = $request->validated();
        $actor = $request->user();

        $tarjeta = null;

        DB::transaction(function () use (&$tarjeta, $data, $actor) {
            $tarjeta = TarjetaNfc::query()->create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'gerencia_id' => $data['gerencia_id'] ?? null,
                'fecha_asignacion' => $data['fecha_asignacion'] ?? ($data['user_id'] ? now() : null),
                'fecha_expiracion' => $data['fecha_expiracion'] ?? null,
                'activo' => $data['activo'] ?? true,
                'asignado_por' => $actor?->id,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // Registrar historial si se creó ya asignada
            if ($tarjeta->user_id) {
                TarjetaNfcAsignacion::query()->create([
                    'tarjeta_nfc_id' => $tarjeta->id,
                    'user_id' => $tarjeta->user_id,
                    'asignado_por' => $actor?->id,
                    'gerencia_id' => $tarjeta->gerencia_id,
                    'fecha_asignacion' => $tarjeta->fecha_asignacion ?? now(),
                    'fecha_desasignacion' => null,
                ]);
            }

            // Asignar puertas/pisos si se enviaron
            $puertas = $data['puertas'] ?? [];
            $pisos = $data['pisos'] ?? [];

            // Si se enviaron pisos, expandir a puertas activas de esos pisos
            if (is_array($pisos) && count($pisos) > 0) {
                $puertas = Puerta::query()
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
                    DB::table('tarjeta_nfc_puerta_acceso')->updateOrInsert(
                        ['tarjeta_nfc_id' => $tarjeta->id, 'puerta_id' => $puertaId],
                        $pivot
                    );
                }
            }
        });

        return redirect()
            ->route('tarjetas-nfc.index')
            ->with('success', 'Tarjeta NFC creada exitosamente.');
    }

    /**
     * Mostrar tarjeta NFC
     */
    public function show(TarjetaNfc $tarjetaNfc): Response
    {
        $this->authorize('view', $tarjetaNfc);

        $tarjetaNfc->load([
            'user.role',
            'gerencia.secretaria.piso',
            'asignadoPor',
            'asignaciones' => function ($q) {
                $q->with(['user.role', 'asignadoPor'])->orderByDesc('fecha_asignacion');
            },
            'puertas' => function ($q) {
                $q->withPivot(['hora_inicio', 'hora_fin', 'dias_semana', 'fecha_inicio', 'fecha_fin', 'activo']);
            },
        ]);

        return Inertia::render('TarjetasNfc/Show', [
            'tarjeta' => [
                'id' => $tarjetaNfc->id,
                'codigo' => $tarjetaNfc->codigo,
                'nombre' => $tarjetaNfc->nombre,
                'user' => $tarjetaNfc->user ? [
                    'id' => $tarjetaNfc->user->id,
                    'name' => $tarjetaNfc->user->name,
                    'email' => $tarjetaNfc->user->email,
                    'n_identidad' => $tarjetaNfc->user->n_identidad,
                    'role' => $tarjetaNfc->user->role ? ['id' => $tarjetaNfc->user->role->id, 'name' => $tarjetaNfc->user->role->name] : null,
                ] : null,
                'gerencia' => $tarjetaNfc->gerencia ? [
                    'id' => $tarjetaNfc->gerencia->id,
                    'nombre' => $tarjetaNfc->gerencia->nombre,
                    'secretaria' => $tarjetaNfc->gerencia->secretaria ? [
                        'id' => $tarjetaNfc->gerencia->secretaria->id,
                        'nombre' => $tarjetaNfc->gerencia->secretaria->nombre,
                        'piso' => $tarjetaNfc->gerencia->secretaria->piso ? [
                            'id' => $tarjetaNfc->gerencia->secretaria->piso->id,
                            'nombre' => $tarjetaNfc->gerencia->secretaria->piso->nombre,
                        ] : null,
                    ] : null,
                ] : null,
                'fecha_asignacion' => $tarjetaNfc->fecha_asignacion?->format('Y-m-d\TH:i'),
                'fecha_expiracion' => $tarjetaNfc->fecha_expiracion?->format('Y-m-d\TH:i'),
                'activo' => (bool) $tarjetaNfc->activo,
                'observaciones' => $tarjetaNfc->observaciones,
                'asignado_por' => $tarjetaNfc->asignadoPor ? [
                    'id' => $tarjetaNfc->asignadoPor->id,
                    'name' => $tarjetaNfc->asignadoPor->name,
                ] : null,
                'puertas' => $tarjetaNfc->puertas->map(fn($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'pivot' => $p->pivot,
                ]),
                'created_at' => $tarjetaNfc->created_at?->format('d/m/Y H:i'),
                'updated_at' => $tarjetaNfc->updated_at?->format('d/m/Y H:i'),
            ],
            'asignaciones' => $tarjetaNfc->asignaciones->map(fn (TarjetaNfcAsignacion $a) => [
                'id' => $a->id,
                'user' => $a->user ? [
                    'id' => $a->user->id,
                    'name' => $a->user->name,
                    'email' => $a->user->email,
                    'n_identidad' => $a->user->n_identidad,
                    'role' => $a->user->role ? ['id' => $a->user->role->id, 'name' => $a->user->role->name] : null,
                ] : null,
                'asignado_por' => $a->asignadoPor ? [
                    'id' => $a->asignadoPor->id,
                    'name' => $a->asignadoPor->name,
                ] : null,
                'gerencia_id' => $a->gerencia_id,
                'fecha_asignacion' => $a->fecha_asignacion?->toIso8601String(),
                'fecha_desasignacion' => $a->fecha_desasignacion?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(TarjetaNfc $tarjetaNfc): Response
    {
        $this->authorize('update', $tarjetaNfc);

        $tarjetaNfc->load(['user.role', 'gerencia.secretaria.piso', 'puertas']);

        $gerencias = Gerencia::query()
            ->where('activo', true)
            ->with('secretaria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'secretaria_id']);

        $secretarias = Secretaria::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'piso_id']);

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        // Lista de usuarios (funcionarios y visitantes) para asignar tarjetas
        $usuarios = User::query()
            ->where('activo', true)
            ->with('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'n_identidad', 'role_id']);

        // Obtener pisos asignados desde las puertas
        $pisosAsignados = $tarjetaNfc->puertas->pluck('piso_id')->unique()->filter()->toArray();

        return Inertia::render('TarjetasNfc/Edit', [
            'tarjeta' => [
                'id' => $tarjetaNfc->id,
                'codigo' => $tarjetaNfc->codigo,
                'nombre' => $tarjetaNfc->nombre,
                'user_id' => $tarjetaNfc->user_id,
                'gerencia_id' => $tarjetaNfc->gerencia_id,
                'secretaria_id' => $tarjetaNfc->gerencia?->secretaria_id ?? null,
                'fecha_asignacion' => $tarjetaNfc->fecha_asignacion?->format('Y-m-d\TH:i'),
                'fecha_expiracion' => $tarjetaNfc->fecha_expiracion?->format('Y-m-d\TH:i'),
                'activo' => (bool) $tarjetaNfc->activo,
                'observaciones' => $tarjetaNfc->observaciones,
                'puertas' => $tarjetaNfc->puertas->pluck('id')->toArray(),
                'pisos' => $pisosAsignados,
            ],
            'gerencias' => $gerencias,
            'secretarias' => $secretarias,
            'pisos' => $pisos,
            'puertas' => $puertas,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Actualizar tarjeta NFC
     */
    public function update(UpdateTarjetaNfcRequest $request, TarjetaNfc $tarjetaNfc): RedirectResponse
    {
        $this->authorize('update', $tarjetaNfc);

        $data = $request->validated();
        $actor = $request->user();
        $previousUserId = $tarjetaNfc->user_id;
        $now = now();

        DB::transaction(function () use ($tarjetaNfc, $data, $previousUserId, $actor, $now) {
            $tarjetaNfc->update([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'gerencia_id' => $data['gerencia_id'] ?? null,
                'fecha_asignacion' => $data['fecha_asignacion'] ?? ($data['user_id'] ? now() : null),
                'fecha_expiracion' => $data['fecha_expiracion'] ?? null,
                'activo' => $data['activo'] ?? true,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $newUserId = $tarjetaNfc->user_id;
            if ((int) ($previousUserId ?? 0) !== (int) ($newUserId ?? 0)) {
                // Cerrar asignación abierta anterior (si existe)
                TarjetaNfcAsignacion::query()
                    ->where('tarjeta_nfc_id', $tarjetaNfc->id)
                    ->whereNull('fecha_desasignacion')
                    ->update([
                        'fecha_desasignacion' => $now,
                        'updated_at' => now(),
                    ]);

                // Crear nueva asignación si aplica
                if ($newUserId) {
                    TarjetaNfcAsignacion::query()->create([
                        'tarjeta_nfc_id' => $tarjetaNfc->id,
                        'user_id' => $newUserId,
                        'asignado_por' => $actor?->id,
                        'gerencia_id' => $tarjetaNfc->gerencia_id,
                        'fecha_asignacion' => $tarjetaNfc->fecha_asignacion ?? $now,
                        'fecha_desasignacion' => null,
                    ]);
                }
            }

            // Actualizar puertas/pisos
            $puertas = $data['puertas'] ?? [];
            $pisos = $data['pisos'] ?? [];

            // Si se enviaron pisos, expandir a puertas activas de esos pisos
            if (is_array($pisos) && count($pisos) > 0) {
                $puertas = Puerta::query()
                    ->where('activo', true)
                    ->whereIn('piso_id', $pisos)
                    ->pluck('id')
                    ->toArray();
            }

            // Eliminar todas las relaciones existentes
            DB::table('tarjeta_nfc_puerta_acceso')
                ->where('tarjeta_nfc_id', $tarjetaNfc->id)
                ->delete();

            // Crear nuevas relaciones
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
                    DB::table('tarjeta_nfc_puerta_acceso')->insert([
                        'tarjeta_nfc_id' => $tarjetaNfc->id,
                        'puerta_id' => $puertaId,
                        ...$pivot,
                    ]);
                }
            }
        });

        return redirect()
            ->route('tarjetas-nfc.show', $tarjetaNfc)
            ->with('message', 'Tarjeta NFC actualizada exitosamente.');
    }

    /**
     * Desasignar tarjeta NFC (dejarla disponible)
     */
    public function desasignar(Request $request, TarjetaNfc $tarjetaNfc): RedirectResponse
    {
        $this->authorize('update', $tarjetaNfc);

        if (!$tarjetaNfc->user_id) {
            return redirect()
                ->route('tarjetas-nfc.show', $tarjetaNfc)
                ->with('success', 'La tarjeta ya estaba sin asignar.');
        }

        $now = now();

        DB::transaction(function () use ($tarjetaNfc, $now) {
            TarjetaNfcAsignacion::query()
                ->where('tarjeta_nfc_id', $tarjetaNfc->id)
                ->whereNull('fecha_desasignacion')
                ->update([
                    'fecha_desasignacion' => $now,
                    'updated_at' => now(),
                ]);

            $tarjetaNfc->update([
                'user_id' => null,
                'gerencia_id' => null,
                'fecha_asignacion' => null,
                'fecha_expiracion' => null,
                'asignado_por' => null,
            ]);
        });

        return redirect()
            ->route('tarjetas-nfc.show', $tarjetaNfc)
            ->with('message', 'Tarjeta NFC desasignada correctamente.');
    }

    /**
     * Eliminar tarjeta NFC
     */
    public function destroy(TarjetaNfc $tarjetaNfc): RedirectResponse
    {
        $this->authorize('delete', $tarjetaNfc);

        $tarjetaNfc->delete();

        return redirect()
            ->route('tarjetas-nfc.index')
            ->with('success', 'Tarjeta NFC eliminada exitosamente.');
    }
}
