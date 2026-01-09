<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cargo;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with(['role', 'cargo', 'departamento.piso', 'createdBy', 'updatedBy', 'creadoPor'])
            ->orderByDesc('id')
            ->paginate(10)
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
                'departamento' => $u->departamento ? ['id' => $u->departamento->id, 'nombre' => $u->departamento->nombre] : null,
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
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'roles' => Role::query()
                ->whereIn('name', ['funcionario', 'visitante'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'cargos' => Cargo::query()->orderBy('name')->get(['id', 'name']),
            'departamentos' => \App\Models\Departamento::query()
                ->where('activo', true)
                ->with('piso')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        // role_id puede venir por role_name
        if (empty($data['role_id']) && !empty($data['role_name'])) {
            $data['role_id'] = Role::query()
                ->whereIn('name', ['funcionario', 'visitante'])
                ->where('name', $data['role_name'])
                ->value('id');
        }

        // Guardar foto (archivo) si viene
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_perfil', 'public');
            $data['foto_perfil'] = $path;
        }

        // Evitar que inputs auxiliares queden en mass assignment
        unset($data['tipo_contrato']);

        // Si es visitante, no debe tener departamento ni fecha de expiración
        $roleName = !empty($data['role_id'])
            ? Role::query()->whereKey($data['role_id'])->value('name')
            : null;

        if ($roleName === 'visitante') {
            $data['cargo_id'] = null;
            $data['departamento_id'] = null;
            $data['fecha_expiracion'] = null;
        }

        // Si el tipo de contrato es indefinido, no debe tener fecha de expiración
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

        // Evitar que array de archivos termine en mass assignment
        unset($data['contratos']);

        // Auditoría
        $actorId = $request->user()?->id;
        $data['creado_por'] = $data['creado_por'] ?? $actorId; // compat (campo legacy)
        $data['created_by'] = $data['created_by'] ?? $actorId;

        $user = User::query()->create($data);

        $tipoContrato = $request->input('tipo_contrato');

        // Subir contratos PDF opcionales
        if ($request->hasFile('contratos')) {
            foreach (($request->file('contratos') ?? []) as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store("user_documentos/{$user->id}/contratos");
                UserDocumento::query()->create([
                    'user_id' => $user->id,
                    'tipo' => 'contrato',
                    'tipo_contrato' => $tipoContrato,
                    'nombre_original' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                    'subido_por' => $request->user()?->id,
                ]);
            }
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado.');
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load([
            'role',
            'cargo',
            'departamento.piso',
            'createdBy',
            'updatedBy',
            'creadoPor',
            'documentos' => function ($q) {
                $q->where('tipo', 'contrato')->orderBy('created_at', 'desc');
            },
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

        $user->load(['role', 'cargo', 'departamento.piso']);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'n_identidad' => $user->n_identidad,
                'departamento_id' => $user->departamento_id,
                'foto_perfil' => $user->foto_perfil,
                'activo' => (bool) ($user->activo ?? true),
                'es_discapacitado' => (bool) ($user->es_discapacitado ?? false),
                'fecha_expiracion' => $user->fecha_expiracion?->format('Y-m-d'),
                'role_id' => $user->role_id,
                'cargo_id' => $user->cargo_id,
            ],
            'documentos' => $user->documentos()
                ->where('tipo', 'contrato')
                ->get()
                ->map(fn(UserDocumento $d) => [
                    'id' => $d->id,
                    'nombre' => $d->nombre_original,
                    'tipo_contrato' => $d->tipo_contrato,
                    'size' => $d->size,
                    'created_at' => $d->created_at?->format('d/m/Y H:i'),
                ]),
            'roles' => Role::query()
                ->whereIn('name', ['funcionario', 'visitante'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'cargos' => Cargo::query()->orderBy('name')->get(['id', 'name']),
            'departamentos' => \App\Models\Departamento::query()
                ->where('activo', true)
                ->with('piso')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (empty($data['role_id']) && !empty($data['role_name'])) {
            $data['role_id'] = Role::query()
                ->whereIn('name', ['funcionario', 'visitante'])
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

        // Evitar que inputs auxiliares queden en mass assignment
        unset($data['tipo_contrato']);

        // Si es visitante, no debe tener departamento ni fecha de expiración
        $roleIdFinal = $data['role_id'] ?? $user->role_id;
        $roleNameFinal = $roleIdFinal ? Role::query()->whereKey($roleIdFinal)->value('name') : null;
        if ($roleNameFinal === 'visitante') {
            $data['cargo_id'] = null;
            $data['departamento_id'] = null;
            $data['fecha_expiracion'] = null;
        }

        // Si el tipo de contrato es indefinido, no debe tener fecha de expiración
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

        // Evitar que array de archivos termine en mass assignment
        unset($data['contratos']);

        // Auditoría (última edición) - debe ir ANTES del update()
        $data['updated_by'] = $request->user()?->id;

        $user->update($data);

        $tipoContrato = $request->input('tipo_contrato');

        // Subir contratos PDF opcionales
        if ($request->hasFile('contratos')) {
            foreach (($request->file('contratos') ?? []) as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store("user_documentos/{$user->id}/contratos");
                UserDocumento::query()->create([
                    'user_id' => $user->id,
                    'tipo' => 'contrato',
                    'tipo_contrato' => $tipoContrato,
                    'nombre_original' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                    'subido_por' => $request->user()?->id,
                ]);
            }
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function downloadDocumento(Request $request, User $user, UserDocumento $documento)
    {
        $this->authorize('update', $user);

        if ($documento->user_id !== $user->id) {
            abort(404);
        }

        if (!Storage::exists($documento->path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $filename = $documento->nombre_original ?: ('contrato_' . $documento->id . '.pdf');
        return Storage::download($documento->path, $filename);
    }

    public function destroyDocumento(Request $request, User $user, UserDocumento $documento): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($documento->user_id !== $user->id) {
            abort(404);
        }

        if ($documento->path && Storage::exists($documento->path)) {
            Storage::delete($documento->path);
        }

        $documento->delete();

        return redirect()->back()->with('success', 'Documento eliminado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
