<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cargo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with(['role', 'cargo', 'departamento.piso'])
            ->orderByDesc('id')
            ->paginate(10)
            ->through(fn(User $u) => [
                'id' => $u->id,
                'email' => $u->email,
                'name' => $u->name,
                'username' => $u->username,
                'nombre' => $u->nombre,
                'apellido' => $u->apellido,
                'activo' => (bool) ($u->activo ?? true),
                'es_discapacitado' => (bool) ($u->es_discapacitado ?? false),
                'fecha_expiracion' => $u->fecha_expiracion?->format('Y-m-d'),
                'role' => $u->role ? ['id' => $u->role->id, 'name' => $u->role->name] : null,
                'cargo' => $u->cargo ? ['id' => $u->cargo->id, 'name' => $u->cargo->name] : null,
                'departamento' => $u->departamento ? ['id' => $u->departamento->id, 'nombre' => $u->departamento->nombre] : null,
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
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
        $data = $request->validated();

        // role_id puede venir por role_name
        if (empty($data['role_id']) && !empty($data['role_name'])) {
            $data['role_id'] = Role::query()->where('name', $data['role_name'])->value('id');
        }

        // Derivar name si no viene explícito
        if (empty($data['name'])) {
            $nombre = trim((string) ($data['nombre'] ?? ''));
            $apellido = trim((string) ($data['apellido'] ?? ''));
            $data['name'] = trim($nombre . ' ' . $apellido) ?: ($data['username'] ?? $data['email']);
        }

        // Defaults
        $data['activo'] = array_key_exists('activo', $data) ? (bool) $data['activo'] : true;
        $data['es_discapacitado'] = array_key_exists('es_discapacitado', $data) ? (bool) $data['es_discapacitado'] : false;

        User::query()->create($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $user): Response
    {
        $user->load(['role', 'cargo', 'departamento.piso']);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'username' => $user->username,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'departamento_id' => $user->departamento_id,
                'foto_perfil' => $user->foto_perfil,
                'activo' => (bool) ($user->activo ?? true),
                'es_discapacitado' => (bool) ($user->es_discapacitado ?? false),
                'fecha_expiracion' => $user->fecha_expiracion?->format('Y-m-d'),
                'role_id' => $user->role_id,
                'cargo_id' => $user->cargo_id,
            ],
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
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
        $data = $request->validated();

        if (empty($data['role_id']) && !empty($data['role_name'])) {
            $data['role_id'] = Role::query()->where('name', $data['role_name'])->value('id');
        }

        // Password opcional
        if (array_key_exists('password', $data) && ($data['password'] === null || $data['password'] === '')) {
            unset($data['password']);
        }

        // Derivar name si no viene y vienen nombre/apellido
        if ((!array_key_exists('name', $data) || empty($data['name'])) && (array_key_exists('nombre', $data) || array_key_exists('apellido', $data))) {
            $nombre = trim((string) ($data['nombre'] ?? $user->nombre ?? ''));
            $apellido = trim((string) ($data['apellido'] ?? $user->apellido ?? ''));
            $data['name'] = trim($nombre . ' ' . $apellido) ?: $user->name;
        }

        $user->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
