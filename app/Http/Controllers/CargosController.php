<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Http\Requests\UpsertCargoPisoAccesoRequest;
use App\Models\Cargo;
use App\Models\Piso;
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
        $this->authorize('viewAny', Cargo::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));
        $search = trim((string) $request->query('search', ''));

        $query = Cargo::query()
            ->withCount('users');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $cargos = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return Inertia::render('Cargos/Index', [
            'cargos' => $cargos,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $this->authorize('create', Cargo::class);

        return Inertia::render('Cargos/Create');
    }

    /**
     * Guardar nuevo cargo
     */
    public function store(StoreCargoRequest $request)
    {
        $this->authorize('create', Cargo::class);

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
        $this->authorize('update', $cargo);

        // Cargar pisos con sus permisos (pivot)
        $pisosAsignados = $cargo->pisos()
            ->withPivot(['hora_inicio', 'hora_fin', 'dias_semana', 'fecha_inicio', 'fecha_fin', 'activo'])
            ->orderBy('orden')
            ->get();

        // Todos los pisos activos para el selector
        $todosLosPisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        // Cargar permisos del sistema
        $cargo->load('permissions');
        $permissions = \App\Models\Permission::query()
            ->where('activo', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();
        $permissionsGrouped = $permissions->groupBy('group')->toArray();

        return Inertia::render('Cargos/Edit', [
            'cargo' => $cargo,
            'pisosAsignados' => $pisosAsignados,
            'todosLosPisos' => $todosLosPisos,
            'permissions' => $permissions,
            'permissionsGrouped' => $permissionsGrouped,
        ]);
    }

    /**
     * Actualizar cargo
     */
    public function update(UpdateCargoRequest $request, Cargo $cargo)
    {
        $this->authorize('update', $cargo);

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
        $this->authorize('delete', $cargo);

        $cargo->delete();

        return redirect()
            ->route('cargos.index')
            ->with('message', 'Cargo eliminado exitosamente.');
    }

    /**
     * Asignar o actualizar permiso de piso a cargo
     */
    public function upsertPiso(UpsertCargoPisoAccesoRequest $request, Cargo $cargo)
    {
        $this->authorize('update', $cargo);

        $data = $request->validated();
        $pisoId = (int) $data['piso_id'];

        $pivot = [
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activo' => $data['activo'] ?? true,
        ];

        $cargo->pisos()->syncWithoutDetaching([
            $pisoId => $pivot,
        ]);

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permiso de piso actualizado.');
    }

    /**
     * Revocar permiso de piso
     */
    public function revokePiso(Cargo $cargo, Piso $piso)
    {
        $this->authorize('update', $cargo);

        $cargo->pisos()->detach($piso->id);

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permiso de piso revocado.');
    }

    /**
     * Actualizar permisos del sistema de un cargo
     */
    public function updatePermissions(Request $request, Cargo $cargo)
    {
        $this->authorize('managePermissions', $cargo);

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $cargo->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permisos del sistema actualizados exitosamente.');
    }
}
