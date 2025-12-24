<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Http\Requests\UpsertCargoPuertaAccesoRequest;
use App\Models\Cargo;
use App\Models\Puerta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CargosController extends Controller
{
    /**
     * Listar cargos
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $cargos = Cargo::query()
            ->withCount('users')
            ->orderBy('name')
            ->paginate($perPage);

        return Inertia::render('Cargos/Index', [
            'cargos' => $cargos,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        return Inertia::render('Cargos/Create');
    }

    /**
     * Guardar nuevo cargo
     */
    public function store(StoreCargoRequest $request)
    {
        $cargo = Cargo::query()->create($request->validated());

        return redirect()
            ->route('cargos.index')
            ->with('message', 'Cargo creado exitosamente.');
    }

    /**
     * Mostrar formulario de edición con permisos
     */
    public function edit(Cargo $cargo): Response
    {
        // Cargar puertas con sus permisos (pivot) y zona
        $puertasAsignadas = $cargo->puertas()
            ->with('zona')
            ->withPivot(['hora_inicio', 'hora_fin', 'dias_semana', 'fecha_inicio', 'fecha_fin', 'activo'])
            ->get();

        // Todas las puertas activas para el selector
        $todasLasPuertas = Puerta::query()
            ->where('activo', true)
            ->with('zona')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Cargos/Edit', [
            'cargo' => $cargo,
            'puertasAsignadas' => $puertasAsignadas,
            'todasLasPuertas' => $todasLasPuertas,
        ]);
    }

    /**
     * Actualizar cargo
     */
    public function update(UpdateCargoRequest $request, Cargo $cargo)
    {
        $cargo->fill($request->validated());
        $cargo->save();

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Cargo actualizado exitosamente.');
    }

    /**
     * Eliminar cargo
     */
    public function destroy(Cargo $cargo)
    {
        $cargo->delete();

        return redirect()
            ->route('cargos.index')
            ->with('message', 'Cargo eliminado exitosamente.');
    }

    /**
     * Asignar o actualizar permiso de puerta a cargo
     */
    public function upsertPuerta(UpsertCargoPuertaAccesoRequest $request, Cargo $cargo)
    {
        $data = $request->validated();
        $puertaId = (int) $data['puerta_id'];

        $pivot = [
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activo' => $data['activo'] ?? true,
        ];

        $cargo->puertas()->syncWithoutDetaching([
            $puertaId => $pivot,
        ]);

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permiso de puerta actualizado.');
    }

    /**
     * Revocar permiso de puerta
     */
    public function revokePuerta(Cargo $cargo, Puerta $puerta)
    {
        $cargo->puertas()->detach($puerta->id);

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permiso de puerta revocado.');
    }
}
