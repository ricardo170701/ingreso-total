<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePuertaRequest;
use App\Http\Requests\UpdatePuertaRequest;
use App\Models\Puerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PuertaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *   path="/api/puertas",
     *   tags={"Puertas"},
     *   summary="Listar puertas",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('view_puertas')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        return response()->json(Puerta::query()->with('zona')->orderBy('id')->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *   path="/api/puertas",
     *   tags={"Puertas"},
     *   summary="Crear puerta (requiere permiso create_puertas)",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"nombre"},
     *     @OA\Property(property="zona_id", type="integer", nullable=true, example=1),
     *     @OA\Property(property="nombre", type="string", example="Entrada P1"),
     *     @OA\Property(property="ubicacion", type="string", nullable=true, example="Piso 1 - Lobby"),
     *     @OA\Property(property="descripcion", type="string", nullable=true, example=null),
     *     @OA\Property(property="codigo_fisico", type="string", nullable=true, example="P1-ENT-01"),
     *     @OA\Property(property="requiere_discapacidad", type="boolean", nullable=true, example=false),
     *     @OA\Property(property="activo", type="boolean", nullable=true, example=true)
     *   )),
     *   @OA\Response(response=201, description="Creado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StorePuertaRequest $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('create_puertas')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $puerta = Puerta::query()->create($request->validated());
        return response()->json(['data' => $puerta->load('zona')], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *   path="/api/puertas/{id}",
     *   tags={"Puertas"},
     *   summary="Ver puerta",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(Puerta $puerta): JsonResponse
    {
        if (!request()->user() || !request()->user()->hasPermission('view_puertas')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json(['data' => $puerta->load('zona')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *   path="/api/puertas/{id}",
     *   tags={"Puertas"},
     *   summary="Actualizar puerta (requiere permiso edit_puertas)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     @OA\Property(property="zona_id", type="integer", nullable=true, example=1),
     *     @OA\Property(property="nombre", type="string", example="Entrada P1"),
     *     @OA\Property(property="ubicacion", type="string", nullable=true, example="Piso 1 - Lobby"),
     *     @OA\Property(property="descripcion", type="string", nullable=true, example=null),
     *     @OA\Property(property="codigo_fisico", type="string", nullable=true, example="P1-ENT-01"),
     *     @OA\Property(property="requiere_discapacidad", type="boolean", nullable=true, example=false),
     *     @OA\Property(property="activo", type="boolean", nullable=true, example=true)
     *   )),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdatePuertaRequest $request, Puerta $puerta): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_puertas')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $puerta->fill($request->validated());
        $puerta->save();

        return response()->json(['data' => $puerta->load('zona')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *   path="/api/puertas/{id}",
     *   tags={"Puertas"},
     *   summary="Eliminar puerta (requiere permiso delete_puertas)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function destroy(Request $request, Puerta $puerta): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('delete_puertas')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $puerta->delete();
        return response()->json(['message' => 'Eliminado.']);
    }
}
