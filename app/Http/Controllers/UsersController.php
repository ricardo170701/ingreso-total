<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cargo;
use App\Models\Role;
use App\Models\Secretaria;
use App\Models\Gerencia;
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
            ->with(['role', 'cargo', 'gerencia.secretaria.piso', 'createdBy', 'updatedBy', 'creadoPor'])
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
                ->whereIn('name', ['funcionario', 'visitante'])
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
                ->whereIn('name', ['funcionario', 'visitante'])
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

        // Evitar que array de archivos termine en mass assignment
        unset($data['contratos']);

        // Auditoría
        $actorId = $request->user()?->id;
        $data['creado_por'] = $data['creado_por'] ?? $actorId; // compat (campo legacy)
        $data['created_by'] = $data['created_by'] ?? $actorId;

        // tipo_contrato se guarda en el usuario incluso sin documento
        // Ya viene en $data desde el request validado
        
        $user = User::query()->create($data);

        // Si hay tipo_contrato pero no hay archivos, crear un registro en user_documentos sin path
        // para que aparezca en el historial y se le pueda agregar un documento después
        if ($tipoContrato && !$request->hasFile('contratos')) {
            // Verificar si ya existe un contrato sin documento con este tipo_contrato
            $existeContratoSinDocumento = UserDocumento::query()
                ->where('user_id', $user->id)
                ->where('tipo', 'contrato')
                ->where('tipo_contrato', $tipoContrato)
                ->whereNull('path')
                ->exists();
            
            if (!$existeContratoSinDocumento) {
                UserDocumento::query()->create([
                    'user_id' => $user->id,
                    'tipo' => 'contrato',
                    'tipo_contrato' => $tipoContrato,
                    'nombre_original' => null,
                    'mime' => null,
                    'size' => null,
                    'path' => null,
                    'subido_por' => $request->user()?->id,
                ]);
            }
        }

        // Subir contratos PDF opcionales (si hay archivos, también guardamos el tipo_contrato en el documento)
        if ($request->hasFile('contratos')) {
            foreach (($request->file('contratos') ?? []) as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store("user_documentos/{$user->id}/contratos");
                UserDocumento::query()->create([
                    'user_id' => $user->id,
                    'tipo' => 'contrato',
                    'tipo_contrato' => $tipoContrato ?? $user->tipo_contrato,
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
            'gerencia.secretaria.piso',
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
                'numero_caso' => $user->numero_caso,
                'gerencia_id' => $user->gerencia_id,
                'secretaria_id' => $user->gerencia?->secretaria_id ?? null,
                'foto_perfil' => $user->foto_perfil,
                'activo' => (bool) ($user->activo ?? true),
                'es_discapacitado' => (bool) ($user->es_discapacitado ?? false),
                'fecha_expiracion' => $user->fecha_expiracion?->format('Y-m-d'),
                'tipo_contrato' => $user->tipo_contrato,
                'role_id' => $user->role_id,
                'cargo_id' => $user->cargo_id,
            ],
            'documentos' => $user->documentos()
                ->where('tipo', 'contrato')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn(UserDocumento $d) => [
                    'id' => $d->id,
                    'nombre' => $d->nombre_original,
                    'tipo_contrato' => $d->tipo_contrato,
                    'size' => $d->size,
                    'path' => $d->path,
                    'created_at' => $d->created_at?->format('d/m/Y H:i'),
                ]),
            'roles' => Role::query()
                ->whereIn('name', ['funcionario', 'visitante'])
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

        // secretaria_id no se guarda, solo se usa para filtrar gerencias en el frontend
        unset($data['secretaria_id']);

        // Si es visitante, no debe tener gerencia ni fecha de expiración
        $roleIdFinal = $data['role_id'] ?? $user->role_id;
        $roleNameFinal = $roleIdFinal ? Role::query()->whereKey($roleIdFinal)->value('name') : null;
        if ($roleNameFinal === 'visitante') {
            $data['cargo_id'] = null;
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

        // Evitar que array de archivos termine en mass assignment
        unset($data['contratos']);

        // Auditoría (última edición) - debe ir ANTES del update()
        $data['updated_by'] = $request->user()?->id;

        // tipo_contrato se guarda en el usuario incluso sin documento
        // Ya viene en $data desde el request validado
        
        $user->update($data);

        // Si hay tipo_contrato pero no hay archivos nuevos, crear/actualizar registro en user_documentos sin path
        // para que aparezca en el historial y se le pueda agregar un documento después
        if ($tipoContrato && !$request->hasFile('contratos')) {
            // Buscar si ya existe un contrato sin documento con el nuevo tipo_contrato
            $contratoSinDocumentoExistente = UserDocumento::query()
                ->where('user_id', $user->id)
                ->where('tipo', 'contrato')
                ->where('tipo_contrato', $tipoContrato)
                ->whereNull('path')
                ->first();
            
            if (!$contratoSinDocumentoExistente) {
                // Si no existe uno con el nuevo tipo_contrato, buscar si hay alguno sin documento con otro tipo
                $contratoSinDocumentoAnterior = UserDocumento::query()
                    ->where('user_id', $user->id)
                    ->where('tipo', 'contrato')
                    ->whereNull('path')
                    ->first();
                
                if ($contratoSinDocumentoAnterior) {
                    // Si existe uno con otro tipo_contrato, actualizarlo al nuevo
                    $contratoSinDocumentoAnterior->update([
                        'tipo_contrato' => $tipoContrato,
                        'subido_por' => $request->user()?->id,
                    ]);
                } else {
                    // Si no existe ninguno sin documento, crear uno nuevo
                    UserDocumento::query()->create([
                        'user_id' => $user->id,
                        'tipo' => 'contrato',
                        'tipo_contrato' => $tipoContrato,
                        'nombre_original' => null,
                        'mime' => null,
                        'size' => null,
                        'path' => null,
                        'subido_por' => $request->user()?->id,
                    ]);
                }
            }
            // Si ya existe uno con el tipo_contrato correcto, no hacer nada
        }

        // Subir contratos PDF opcionales (si hay archivos, también guardamos el tipo_contrato en el documento)
        if ($request->hasFile('contratos')) {
            foreach (($request->file('contratos') ?? []) as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store("user_documentos/{$user->id}/contratos");
                UserDocumento::query()->create([
                    'user_id' => $user->id,
                    'tipo' => 'contrato',
                    'tipo_contrato' => $tipoContrato ?? $user->tipo_contrato,
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

        if (!$documento->path || !Storage::exists($documento->path)) {
            abort(404, 'Archivo no encontrado o no hay documento adjunto.');
        }

        $filename = $documento->nombre_original ?: ('contrato_' . $documento->id . '.pdf');
        return Storage::download($documento->path, $filename);
    }

    public function updateDocumento(Request $request, User $user, UserDocumento $documento): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($documento->user_id !== $user->id) {
            abort(404);
        }

        $request->validate([
            'documento' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if (!$request->hasFile('documento')) {
            return redirect()->back()->with('error', 'No se proporcionó ningún archivo.');
        }

        $file = $request->file('documento');

        // Si ya existe un archivo, eliminarlo
        if ($documento->path && Storage::exists($documento->path)) {
            Storage::delete($documento->path);
        }

        $path = $file->store("user_documentos/{$user->id}/contratos");

        $documento->update([
            'nombre_original' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'subido_por' => $request->user()?->id,
        ]);

        return redirect()->back()->with('success', 'Documento agregado correctamente.');
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
