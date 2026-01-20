<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cargo;
use App\Models\Role;
use App\Models\Secretaria;
use App\Models\Gerencia;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $search = trim((string) request()->query('search', ''));
        $search = Str::of($search)->limit(100)->toString();

        $q = User::query()
            ->with(['role', 'cargo', 'gerencia.secretaria.piso', 'createdBy', 'updatedBy', 'creadoPor'])
            ->orderByDesc('id');

        // Buscador (nombre, email, nombre/apellido, identidad, observaciones)
        if ($search !== '') {
            $q->where(function ($sub) use ($search) {
                $sub->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido', 'like', '%' . $search . '%')
                    ->orWhere('n_identidad', 'like', '%' . $search . '%')
                    ->orWhere('observaciones', 'like', '%' . $search . '%');
            });
        }

        $users = $q->paginate(10)->withQueryString()
            ->through(fn(User $u) => [
                'id' => $u->id,
                'email' => $u->email,
                'name' => $u->name,
                'nombre' => $u->nombre,
                'apellido' => $u->apellido,
                'activo' => (bool) ($u->activo ?? true),
                'es_discapacitado' => (bool) ($u->es_discapacitado ?? false),
                'fecha_expiracion' => $u->fecha_expiracion?->format('Y-m-d'),
                'role' => $u->role ? ['id' => $u->role->id, 'name' => $u->role->name] : null,
                'cargo' => $u->cargo ? ['id' => $u->cargo->id, 'name' => $u->cargo->name] : null,
                'cargo_texto' => $u->cargo_texto,
                'foto_perfil' => $u->foto_perfil,
                'gerencia' => $u->gerencia ? [
                    'id' => $u->gerencia->id,
                    'nombre' => $u->gerencia->nombre,
                    'secretaria' => $u->gerencia->secretaria ? [
                        'id' => $u->gerencia->secretaria->id,
                        'nombre' => $u->gerencia->secretaria->nombre,
                        'piso' => $u->gerencia->secretaria->piso ? [
                            'id' => $u->gerencia->secretaria->piso->id,
                            'nombre' => $u->gerencia->secretaria->piso->nombre,
                        ] : null,
                    ] : null,
                ] : null,
                // Auditoría (nuevo): created_by / updated_by + fechas
                'created_by_name' => $u->createdBy?->name
                    ?? $u->createdBy?->email
                    ?? $u->creadoPor?->name
                    ?? $u->creadoPor?->email
                    ?? null,
                'updated_by_name' => $u->updatedBy?->name ?? $u->updatedBy?->email ?? null,
                'created_at' => $u->created_at?->format('d/m/Y H:i'),
                'updated_at' => $u->updated_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        // Cargar todas las gerencias activas para que el frontend pueda filtrar
        $gerencias = Gerencia::query()
            ->where('activo', true)
            ->with('secretaria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'secretaria_id']);

        return Inertia::render('Users/Create', [
            'roles' => Role::query()
                ->whereIn('name', ['visitante', 'servidor_publico', 'proveedor', 'funcionario'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'cargos' => Cargo::query()->orderBy('name')->get(['id', 'name']),
            'secretarias' => Secretaria::query()
                ->where('activo', true)
                ->with('piso')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'piso_id']),
            'gerencias' => $gerencias,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        // role_id puede venir por role_name
        if (empty($data['role_id']) && !empty($data['role_name'])) {
            $data['role_id'] = Role::query()
                ->whereIn('name', ['visitante', 'servidor_publico', 'proveedor', 'funcionario'])
                ->where('name', $data['role_name'])
                ->value('id');
        }

        // Guardar foto (archivo) si viene
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_perfil', 'public');
            $data['foto_perfil'] = $path;
        }

        // secretaria_id no se guarda en la BD, solo se usa para filtrar gerencias en el frontend
        unset($data['secretaria_id']);

        // Si es visitante, no debe tener gerencia ni fecha de expiración
        $roleName = !empty($data['role_id'])
            ? Role::query()->whereKey($data['role_id'])->value('name')
            : null;

        if ($roleName === 'visitante') {
            $data['cargo_id'] = null;
            $data['cargo_texto'] = null;
            $data['gerencia_id'] = null;
            $data['fecha_expiracion'] = null;
        }

        // Si el tipo de contrato es indefinido, no debe tener fecha de expiración
        // tipo_contrato ahora se guarda en el usuario incluso sin documento
        $tipoContrato = $request->input('tipo_contrato');
        if ($tipoContrato === 'contrato_indefinido') {
            $data['fecha_expiracion'] = null;
        }

        // Derivar name si no viene explícito
        if (empty($data['name'])) {
            $nombre = trim((string) ($data['nombre'] ?? ''));
            $apellido = trim((string) ($data['apellido'] ?? ''));
            $data['name'] = trim($nombre . ' ' . $apellido) ?: ($data['email'] ?? 'Usuario');
        }

        // Defaults
        $data['activo'] = array_key_exists('activo', $data) ? (bool) $data['activo'] : true;
        $data['es_discapacitado'] = array_key_exists('es_discapacitado', $data) ? (bool) $data['es_discapacitado'] : false;

        // Auditoría
        $actorId = $request->user()?->id;
        $data['creado_por'] = $data['creado_por'] ?? $actorId; // compat (campo legacy)
        $data['created_by'] = $data['created_by'] ?? $actorId;

        // tipo_contrato se guarda en el usuario (sin documentos PDF)
        // Ya viene en $data desde el request validado

        $user = User::query()->create($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado.');
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load([
            'role',
            'cargo',
            'gerencia.secretaria.piso',
            'createdBy',
            'updatedBy',
            'creadoPor',
        ]);

        // Agregar nombres de auditoría para facilitar el acceso en la vista
        $userArray = $user->toArray();
        unset($userArray['username']);
        $userArray['created_by_name'] = $user->createdBy?->name ?? $user->createdBy?->email ?? $user->creadoPor?->name ?? $user->creadoPor?->email ?? null;
        $userArray['updated_by_name'] = $user->updatedBy?->name ?? $user->updatedBy?->email ?? null;

        return Inertia::render('Users/Show', [
            'user' => $userArray,
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['role', 'cargo', 'gerencia.secretaria.piso']);

        // Cargar todas las gerencias activas para que el frontend pueda filtrar
        $gerencias = Gerencia::query()
            ->where('activo', true)
            ->with('secretaria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'secretaria_id']);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'n_identidad' => $user->n_identidad,
                'observaciones' => $user->observaciones,
                'gerencia_id' => $user->gerencia_id,
                'secretaria_id' => $user->gerencia?->secretaria_id ?? null,
                'foto_perfil' => $user->foto_perfil,
                'activo' => (bool) ($user->activo ?? true),
                'es_discapacitado' => (bool) ($user->es_discapacitado ?? false),
                'fecha_expiracion' => $user->fecha_expiracion?->format('Y-m-d'),
                'tipo_contrato' => $user->tipo_contrato,
                'role_id' => $user->role_id,
            'cargo_id' => $user->cargo_id,
            'cargo_texto' => $user->cargo_texto,
        ],
        'roles' => Role::query()
                ->whereIn('name', ['visitante', 'servidor_publico', 'proveedor', 'funcionario'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'cargos' => Cargo::query()->orderBy('name')->get(['id', 'name']),
            'secretarias' => Secretaria::query()
                ->where('activo', true)
                ->with('piso')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'piso_id']),
            'gerencias' => $gerencias,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (empty($data['role_id']) && !empty($data['role_name'])) {
            $data['role_id'] = Role::query()
                ->whereIn('name', ['visitante', 'servidor_publico', 'proveedor', 'funcionario'])
                ->where('name', $data['role_name'])
                ->value('id');
        }

        // Password opcional
        if (array_key_exists('password', $data) && ($data['password'] === null || $data['password'] === '')) {
            unset($data['password']);
        }

        // Guardar foto (archivo) si viene
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_perfil', 'public');
            $data['foto_perfil'] = $path;
        }

        // secretaria_id no se guarda, solo se usa para filtrar gerencias en el frontend
        unset($data['secretaria_id']);

        // Si es visitante, no debe tener gerencia ni fecha de expiración
        $roleIdFinal = $data['role_id'] ?? $user->role_id;
        $roleNameFinal = $roleIdFinal ? Role::query()->whereKey($roleIdFinal)->value('name') : null;
        if ($roleNameFinal === 'visitante') {
            $data['cargo_id'] = null;
            $data['cargo_texto'] = null;
            $data['gerencia_id'] = null;
            $data['fecha_expiracion'] = null;
        }

        // Si el tipo de contrato es indefinido, no debe tener fecha de expiración
        // tipo_contrato ahora se guarda en el usuario incluso sin documento
        $tipoContrato = $request->input('tipo_contrato');
        if ($tipoContrato === 'contrato_indefinido') {
            $data['fecha_expiracion'] = null;
        }

        // Derivar name si no viene y vienen nombre/apellido
        if ((!array_key_exists('name', $data) || empty($data['name'])) && (array_key_exists('nombre', $data) || array_key_exists('apellido', $data))) {
            $nombre = trim((string) ($data['nombre'] ?? $user->nombre ?? ''));
            $apellido = trim((string) ($data['apellido'] ?? $user->apellido ?? ''));
            $data['name'] = trim($nombre . ' ' . $apellido) ?: $user->name;
        }

        // Auditoría (última edición) - debe ir ANTES del update()
        $data['updated_by'] = $request->user()?->id;

        // tipo_contrato se guarda en el usuario (sin documentos PDF)
        // Ya viene en $data desde el request validado

        $user->update($data);

        return redirect()->route('usuarios.show', $user)->with('message', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
