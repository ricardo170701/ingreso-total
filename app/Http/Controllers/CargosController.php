<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Http\Requests\UpsertCargoPisoAccesoRequest;
use App\Http\Requests\UpsertCargoPuertaAccesoRequest;
use App\Models\Cargo;
use App\Models\Piso;
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

        // Cargar permisos del sistema para el formulario
        $permissions = \App\Models\Permission::query()
            ->where('activo', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();
        $permissionsGrouped = $permissions->groupBy('group')->toArray();

        // Cargar pisos activos para el formulario
        $todosLosPisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Cargos/Create', [
            'permissions' => $permissions,
            'permissionsGrouped' => $permissionsGrouped,
            'todosLosPisos' => $todosLosPisos,
        ]);
    }

    /**
     * Guardar nuevo cargo
     */
    public function store(StoreCargoRequest $request)
    {
        $this->authorize('create', Cargo::class);

        $data = $request->validated();
        $permissions = $data['permissions'] ?? [];
        $pisos = $data['pisos'] ?? [];

        // Extraer campos de configuración de pisos
        $pisoConfig = [
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activo' => true,
        ];

        // Eliminar campos que no son del modelo Cargo
        unset($data['permissions'], $data['pisos'], $data['hora_inicio'], $data['hora_fin'], $data['dias_semana'], $data['fecha_inicio'], $data['fecha_fin']);

        $cargo = Cargo::query()->create($data);

        // Sincronizar permisos si se proporcionaron
        if (!empty($permissions)) {
            $cargo->permissions()->sync($permissions);
        }

        // Sincronizar pisos si se proporcionaron
        if (!empty($pisos) && is_array($pisos)) {
            $pisoIds = array_values(array_unique(array_map('intval', $pisos)));
            $syncData = [];
            foreach ($pisoIds as $pisoId) {
                $syncData[$pisoId] = $pisoConfig;
            }
            if (count($syncData) > 0) {
                $cargo->pisos()->sync($syncData);
            }
        }

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

        // Puertas asignadas individualmente (cargo_puerta_acceso)
        $puertasAsignadas = $cargo->puertas()
            ->withPivot(['hora_inicio', 'hora_fin', 'dias_semana', 'fecha_inicio', 'fecha_fin', 'activo'])
            ->with('piso')
            ->orderBy('nombre')
            ->get();

        // Todos los pisos activos para el selector
        $todosLosPisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        // Pisos con sus puertas activas (para selección por puerta individual)
        $pisosConPuertas = Piso::query()
            ->where('activo', true)
            ->with(['puertas' => fn($q) => $q->where('activo', true)->orderBy('nombre')])
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
            'puertasAsignadas' => $puertasAsignadas,
            'todosLosPisos' => $todosLosPisos,
            'pisosConPuertas' => $pisosConPuertas,
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
    public function destroy(Request $request, Cargo $cargo)
    {
        $this->authorize('delete', $cargo);

        // Validar que se proporcione la contraseña
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verificar que la contraseña sea correcta
        $user = $request->user();
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'La contraseña es incorrecta.',
            ]);
        }

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
        $pisoIds = [];
        if (!empty($data['pisos']) && is_array($data['pisos'])) {
            $pisoIds = array_values(array_unique(array_map('intval', $data['pisos'])));
        } elseif (!empty($data['piso_id'])) {
            $pisoIds = [(int) $data['piso_id']];
        }

        $pivot = [
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activo' => $data['activo'] ?? true,
        ];

        $syncData = [];
        foreach ($pisoIds as $pid) {
            $syncData[$pid] = $pivot;
        }

        if (count($syncData) > 0) {
            $cargo->pisos()->syncWithoutDetaching($syncData);
        }

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', count($pisoIds) > 1 ? 'Permisos de pisos actualizados.' : 'Permiso de piso actualizado.');
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
     * Asignar o actualizar permiso de puertas individuales al cargo
     */
    public function upsertPuertas(UpsertCargoPuertaAccesoRequest $request, Cargo $cargo)
    {
        $this->authorize('update', $cargo);

        $data = $request->validated();
        $puertaIds = [];
        if (!empty($data['puertas']) && is_array($data['puertas'])) {
            $puertaIds = array_values(array_unique(array_map('intval', $data['puertas'])));
        } elseif (!empty($data['puerta_id'])) {
            $puertaIds = [(int) $data['puerta_id']];
        }

        $pivot = [
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? '1,2,3,4,5,6,7',
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activo' => $data['activo'] ?? true,
        ];

        $syncData = [];
        foreach ($puertaIds as $pid) {
            $syncData[$pid] = $pivot;
        }

        if (count($syncData) > 0) {
            $cargo->puertas()->syncWithoutDetaching($syncData);
        }

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', count($puertaIds) > 1 ? 'Permisos de puertas actualizados.' : 'Permiso de puerta actualizado.');
    }

    /**
     * Revocar permiso de puerta individual
     */
    public function revokePuerta(Cargo $cargo, Puerta $puerta)
    {
        $this->authorize('update', $cargo);

        $cargo->puertas()->detach($puerta->id);

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permiso de puerta revocado.');
    }

    /**
     * Sincronizar lista completa de puertas del cargo (permisos por puerta).
     * Acepta puertas: [1, 2, 3, ...] y reemplaza la asignación actual.
     */
    public function syncPuertas(Request $request, Cargo $cargo)
    {
        $this->authorize('update', $cargo);

        $request->validate([
            'puertas' => ['present', 'array'],
            'puertas.*' => ['integer', 'exists:puertas,id'],
        ]);

        $puertaIds = array_values(array_unique(array_map('intval', $request->input('puertas', []))));
        $pivot = [
            'hora_inicio' => null,
            'hora_fin' => null,
            'dias_semana' => '1,2,3,4,5,6,7',
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'activo' => true,
        ];
        $syncData = [];
        foreach ($puertaIds as $pid) {
            $syncData[$pid] = $pivot;
        }
        $cargo->puertas()->sync($syncData);

        return redirect()
            ->route('cargos.edit', $cargo)
            ->with('message', 'Permisos a puertas actualizados.');
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
