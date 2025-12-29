<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Http\Requests\UpsertCargoPuertaAccesoRequest;
use App\Models\Cargo;
use App\Models\Puerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *   path="/api/cargos",
     *   tags={"Cargos"},
     *   summary="Listar cargos",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('view_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        return response()->json(Cargo::query()->orderBy('name')->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *   path="/api/cargos",
     *   tags={"Cargos"},
     *   summary="Crear cargo (requiere permiso create_cargos)",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"name"},
     *     @OA\Property(property="name", type="string", example="Funcionario Medio"),
     *     @OA\Property(property="description", type="string", nullable=true, example="Acceso medio"),
     *     @OA\Property(property="activo", type="boolean", nullable=true, example=true)
     *   )),
     *   @OA\Response(response=201, description="Creado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StoreCargoRequest $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('create_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $cargo = Cargo::query()->create($request->validated());
        return response()->json(['data' => $cargo], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *   path="/api/cargos/{id}",
     *   tags={"Cargos"},
     *   summary="Ver cargo",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(Cargo $cargo): JsonResponse
    {
        if (!request()->user() || !request()->user()->hasPermission('view_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json(['data' => $cargo]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *   path="/api/cargos/{id}",
     *   tags={"Cargos"},
     *   summary="Actualizar cargo (requiere permiso edit_cargos)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     @OA\Property(property="name", type="string", example="Funcionario Medio"),
     *     @OA\Property(property="description", type="string", nullable=true, example="Acceso medio"),
     *     @OA\Property(property="activo", type="boolean", nullable=true, example=true)
     *   )),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdateCargoRequest $request, Cargo $cargo): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $cargo->fill($request->validated());
        $cargo->save();

        return response()->json(['data' => $cargo]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *   path="/api/cargos/{id}",
     *   tags={"Cargos"},
     *   summary="Eliminar cargo (requiere permiso delete_cargos)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function destroy(Request $request, Cargo $cargo): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('delete_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $cargo->delete();
        return response()->json(['message' => 'Eliminado.']);
    }

    /**
     * Listar puertas (permisos) asociadas a un cargo con su configuración (pivot).
     *
     * @OA\Get(
     *   path="/api/cargos/{id}/puertas",
     *   tags={"Cargos"},
     *   summary="Listar permisos de puertas por cargo",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function puertas(Cargo $cargo): JsonResponse
    {
        if (!request()->user() || !request()->user()->hasPermission('view_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $puertas = $cargo->puertas()
            ->withPivot(['hora_inicio', 'hora_fin', 'dias_semana', 'fecha_inicio', 'fecha_fin', 'activo'])
            ->get();

        return response()->json([
            'data' => $puertas,
        ]);
    }

    /**
     * Asignar o actualizar una puerta a un cargo con reglas de horario (upsert).
     *
     * @OA\Post(
     *   path="/api/cargos/{id}/puertas",
     *   tags={"Cargos"},
     *   summary="Asignar/actualizar permiso de puerta a cargo (requiere permiso edit_cargos)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"puerta_id"},
     *     @OA\Property(property="puerta_id", type="integer", example=1),
     *     @OA\Property(property="hora_inicio", type="string", nullable=true, example="08:00"),
     *     @OA\Property(property="hora_fin", type="string", nullable=true, example="18:00"),
     *     @OA\Property(property="dias_semana", type="string", nullable=true, example="1,2,3,4,5"),
     *     @OA\Property(property="fecha_inicio", type="string", format="date", nullable=true, example=null),
     *     @OA\Property(property="fecha_fin", type="string", format="date", nullable=true, example=null),
     *     @OA\Property(property="activo", type="boolean", nullable=true, example=true)
     *   )),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function upsertPuerta(UpsertCargoPuertaAccesoRequest $request, Cargo $cargo): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validated();
        $puertaId = (int) $data['puerta_id'];

        // Aseguramos existencia (por si se quiere cargar el recurso al final)
        $puerta = Puerta::query()->findOrFail($puertaId);

        $pivot = [
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activo' => $data['activo'] ?? true,
        ];

        // Upsert: no detacha otros permisos
        $cargo->puertas()->syncWithoutDetaching([
            $puertaId => $pivot,
        ]);

        return response()->json([
            'message' => 'Permiso actualizado.',
            'data' => $cargo->puertas()
                ->whereKey($puerta->getKey())
                ->withPivot(['hora_inicio', 'hora_fin', 'dias_semana', 'fecha_inicio', 'fecha_fin', 'activo'])
                ->first(),
        ]);
    }

    /**
     * Revocar permiso de una puerta en un cargo.
     *
     * @OA\Delete(
     *   path="/api/cargos/{id}/puertas/{puertaId}",
     *   tags={"Cargos"},
     *   summary="Revocar permiso de puerta en cargo (requiere permiso edit_cargos)",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="puertaId", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function revokePuerta(Request $request, Cargo $cargo, Puerta $puerta): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasPermission('edit_cargos')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $cargo->puertas()->detach($puerta->getKey());
        return response()->json(['message' => 'Permiso revocado.']);
    }
}
