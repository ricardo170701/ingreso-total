<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Listar usuarios",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $q = User::query()
            ->with(['role', 'cargo'])
            ->orderByDesc('id');

        return response()->json($q->paginate($perPage));
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Ver usuario",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="No encontrado"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => $user->load(['role', 'cargo'])]);
    }

    /**
     * POST /api/users
     * Crea un usuario (funcionario/visitante/operador/rrhh/etc.) según reglas del negocio.
     *
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Crear un usuario",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="nuevo@local.test"),
     *             @OA\Property(property="password", type="string", example="demo12345"),
     *             @OA\Property(property="role_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="role_name", type="string", nullable=true, example="visitante"),
     *             @OA\Property(property="cargo_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="name", type="string", nullable=true, example="Nuevo Usuario"),
     *             @OA\Property(property="username", type="string", nullable=true, example="nuevo1"),
     *             @OA\Property(property="nombre", type="string", nullable=true, example="Nuevo"),
     *             @OA\Property(property="apellido", type="string", nullable=true, example="Usuario"),
     *             @OA\Property(property="departamento", type="string", nullable=true, example="Seguridad"),
     *             @OA\Property(property="foto_perfil", type="string", nullable=true, example=null),
     *             @OA\Property(property="activo", type="boolean", nullable=true, example=true),
     *             @OA\Property(property="fecha_expiracion", type="string", format="date", nullable=true, example=null),
     *             @OA\Property(property="es_discapacitado", type="boolean", nullable=true, example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Creado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario creado correctamente."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="email", type="string", example="nuevo@local.test"),
     *                 @OA\Property(property="role_id", type="integer", nullable=true, example=5),
     *                 @OA\Property(property="cargo_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="name", type="string", example="Nuevo Usuario"),
     *                 @OA\Property(property="activo", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        // Resolver rol (por id o name)
        $roleId = $data['role_id'] ?? null;
        if (!$roleId && !empty($data['role_name'])) {
            $roleId = Role::query()->where('name', $data['role_name'])->value('id');
        }

        // Reglas mínimas por rol del actor (permisología de la app)
        // - super_usuario: puede crear cualquiera
        // - rrhh: no puede crear visitantes
        // - operador: solo crea visitantes
        $actorRole = $actor?->role?->name;
        $targetRole = $roleId ? Role::query()->whereKey($roleId)->value('name') : null;

        if ($actorRole !== 'super_usuario') {
            if ($actorRole === 'rrhh' && $targetRole === 'visitante') {
                return response()->json(['message' => 'RRHH no puede crear visitantes.'], 403);
            }
            if ($actorRole === 'operador' && $targetRole !== 'visitante') {
                return response()->json(['message' => 'Operador solo puede crear visitantes.'], 403);
            }
        }

        // Campo name: si no viene, intentamos derivarlo de nombre/apellido
        $name = $data['name'] ?? trim(($data['nombre'] ?? '') . ' ' . ($data['apellido'] ?? ''));
        if ($name === '') {
            $name = 'Usuario';
        }

        $user = User::query()->create([
            'email' => $data['email'],
            'password' => $data['password'], // se hashea por cast "hashed"

            'role_id' => $roleId,
            'cargo_id' => $data['cargo_id'] ?? null,

            'name' => $name,
            'username' => $data['username'] ?? null,
            'nombre' => $data['nombre'] ?? null,
            'apellido' => $data['apellido'] ?? null,
            'departamento' => $data['departamento'] ?? null,
            'foto_perfil' => $data['foto_perfil'] ?? null,

            'activo' => $data['activo'] ?? true,
            'fecha_expiracion' => $data['fecha_expiracion'] ?? null,
            'es_discapacitado' => $data['es_discapacitado'] ?? false,

            'creado_por' => $actor?->id,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'cargo_id' => $user->cargo_id,
                'name' => $user->name,
                'activo' => $user->activo,
            ],
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Actualizar usuario",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="email", type="string", format="email", example="nuevo@local.test"),
     *         @OA\Property(property="password", type="string", nullable=true, example="demo12345"),
     *         @OA\Property(property="role_id", type="integer", nullable=true, example=5),
     *         @OA\Property(property="role_name", type="string", nullable=true, example="visitante"),
     *         @OA\Property(property="cargo_id", type="integer", nullable=true, example=1),
     *         @OA\Property(property="name", type="string", nullable=true, example="Nuevo Usuario"),
     *         @OA\Property(property="username", type="string", nullable=true, example="nuevo1"),
     *         @OA\Property(property="nombre", type="string", nullable=true, example="Nuevo"),
     *         @OA\Property(property="apellido", type="string", nullable=true, example="Usuario"),
     *         @OA\Property(property="departamento", type="string", nullable=true, example="Seguridad"),
     *         @OA\Property(property="activo", type="boolean", nullable=true, example=true),
     *         @OA\Property(property="fecha_expiracion", type="string", format="date", nullable=true, example=null),
     *         @OA\Property(property="es_discapacitado", type="boolean", nullable=true, example=false)
     *     )),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        // Resolver rol (por id o name)
        $roleId = $data['role_id'] ?? null;
        if (!$roleId && !empty($data['role_name'])) {
            $roleId = Role::query()->where('name', $data['role_name'])->value('id');
        }

        $actorRole = $actor?->role?->name;
        $targetRole = $roleId ? Role::query()->whereKey($roleId)->value('name') : ($user->role?->name);

        // Reglas mínimas por rol del actor
        if ($actorRole !== 'super_usuario') {
            if ($actorRole === 'rrhh' && $targetRole === 'visitante') {
                return response()->json(['message' => 'RRHH no puede modificar visitantes.'], 403);
            }
            if ($actorRole === 'operador' && $targetRole !== 'visitante') {
                return response()->json(['message' => 'Operador solo puede modificar visitantes.'], 403);
            }
        }

        // Campo name: si viene vacío y hay nombre/apellido, lo derivamos
        if (array_key_exists('name', $data) && ($data['name'] === null || trim((string) $data['name']) === '')) {
            $data['name'] = trim(($data['nombre'] ?? '') . ' ' . ($data['apellido'] ?? ''));
        }

        $user->fill([
            'email' => $data['email'] ?? $user->email,
            'password' => $data['password'] ?? null,
            'role_id' => $roleId ?? $user->role_id,
            'cargo_id' => $data['cargo_id'] ?? $user->cargo_id,
            'name' => $data['name'] ?? $user->name,
            'username' => $data['username'] ?? $user->username,
            'nombre' => $data['nombre'] ?? $user->nombre,
            'apellido' => $data['apellido'] ?? $user->apellido,
            'departamento' => $data['departamento'] ?? $user->departamento,
            'foto_perfil' => $data['foto_perfil'] ?? $user->foto_perfil,
            'activo' => array_key_exists('activo', $data) ? ($data['activo'] ?? $user->activo) : $user->activo,
            'fecha_expiracion' => $data['fecha_expiracion'] ?? $user->fecha_expiracion,
            'es_discapacitado' => array_key_exists('es_discapacitado', $data) ? ($data['es_discapacitado'] ?? $user->es_discapacitado) : $user->es_discapacitado,
        ]);

        // Si no se envió password, evitamos sobreescribirlo con null
        if (!array_key_exists('password', $data) || $data['password'] === null) {
            unset($user->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Usuario actualizado.',
            'data' => $user->fresh()->load(['role', 'cargo']),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Eliminar usuario (solo super_usuario)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado")
     * )
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if (($request->user()?->role?->name ?? null) !== 'super_usuario') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Evitar que un super usuario se elimine a sí mismo accidentalmente
        if ($request->user()?->id === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado.']);
    }
}
