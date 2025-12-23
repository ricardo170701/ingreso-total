<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreZonaRequest;
use App\Http\Requests\UpdateZonaRequest;
use App\Models\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *   path="/api/zonas",
     *   tags={"Zonas"},
     *   summary="Listar zonas",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        return response()->json(Zona::query()->orderBy('codigo')->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *   path="/api/zonas",
     *   tags={"Zonas"},
     *   summary="Crear zona (solo super_usuario)",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"nombre","codigo"},
     *     @OA\Property(property="nombre", type="string", example="Piso 4 - Laboratorio"),
     *     @OA\Property(property="codigo", type="string", example="P4"),
     *     @OA\Property(property="descripcion", type="string", nullable=true, example="Área restringida"),
     *     @OA\Property(property="nivel_seguridad", type="integer", nullable=true, example=3),
     *     @OA\Property(property="activa", type="boolean", nullable=true, example=true),
     *     @OA\Property(property="ubicacion_gps", type="string", nullable=true, example=null)
     *   )),
     *   @OA\Response(response=201, description="Creado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StoreZonaRequest $request): JsonResponse
    {
        if (($request->user()?->role?->name ?? null) !== 'super_usuario') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $zona = Zona::query()->create($request->validated());
        return response()->json(['data' => $zona], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *   path="/api/zonas/{id}",
     *   tags={"Zonas"},
     *   summary="Ver zona",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(Zona $zona): JsonResponse
    {
        return response()->json(['data' => $zona]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *   path="/api/zonas/{id}",
     *   tags={"Zonas"},
     *   summary="Actualizar zona (solo super_usuario)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     @OA\Property(property="nombre", type="string", example="Piso 4 - Laboratorio"),
     *     @OA\Property(property="codigo", type="string", example="P4"),
     *     @OA\Property(property="descripcion", type="string", nullable=true, example="Área restringida"),
     *     @OA\Property(property="nivel_seguridad", type="integer", nullable=true, example=3),
     *     @OA\Property(property="activa", type="boolean", nullable=true, example=true),
     *     @OA\Property(property="ubicacion_gps", type="string", nullable=true, example=null)
     *   )),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdateZonaRequest $request, Zona $zona): JsonResponse
    {
        if (($request->user()?->role?->name ?? null) !== 'super_usuario') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $zona->fill($request->validated());
        $zona->save();

        return response()->json(['data' => $zona]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *   path="/api/zonas/{id}",
     *   tags={"Zonas"},
     *   summary="Eliminar zona (solo super_usuario)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function destroy(Request $request, Zona $zona): JsonResponse
    {
        if (($request->user()?->role?->name ?? null) !== 'super_usuario') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $zona->delete();
        return response()->json(['message' => 'Eliminado.']);
    }
}
