<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *   path="/api/roles",
     *   tags={"Roles"},
     *   summary="Listar roles",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('view_roles')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        return response()->json(
            Role::query()
                ->whereIn('name', ['funcionario', 'visitante'])
                ->orderBy('name')
                ->paginate($perPage)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *   path="/api/roles",
     *   tags={"Roles"},
     *   summary="Crear rol (requiere permiso edit_roles)",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"name"},
     *     @OA\Property(property="name", type="string", example="operador"),
     *     @OA\Property(property="description", type="string", nullable=true, example="Seguridad")
     *   )),
     *   @OA\Response(response=201, description="Creado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_roles')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'message' => 'Operación no permitida. Los roles son fijos: funcionario/visitante.',
        ], 403);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *   path="/api/roles/{id}",
     *   tags={"Roles"},
     *   summary="Ver rol",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(Role $role): JsonResponse
    {
        if (!request()->user() || !request()->user()->hasPermission('view_roles')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (!in_array($role->name, ['funcionario', 'visitante'], true)) {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        return response()->json(['data' => $role]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *   path="/api/roles/{id}",
     *   tags={"Roles"},
     *   summary="Actualizar rol (requiere permiso edit_roles)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     @OA\Property(property="name", type="string", example="operador"),
     *     @OA\Property(property="description", type="string", nullable=true, example="Seguridad")
     *   )),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_roles')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'message' => 'Operación no permitida. Los roles son fijos: funcionario/visitante.',
        ], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *   path="/api/roles/{id}",
     *   tags={"Roles"},
     *   summary="Eliminar rol (requiere permiso edit_roles)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_roles')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'message' => 'Operación no permitida. Los roles son fijos: funcionario/visitante.',
        ], 403);
    }
}
