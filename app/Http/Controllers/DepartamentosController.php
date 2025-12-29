<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Piso;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartamentosController extends Controller
{
    /**
     * Listar departamentos
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Departamento::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $departamentos = Departamento::query()
            ->with('piso')
            ->withCount('users')
            ->orderBy('nombre')
            ->paginate($perPage);

        return Inertia::render('Departamentos/Index', [
            'departamentos' => $departamentos,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $this->authorize('create', Departamento::class);

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return Inertia::render('Departamentos/Create', [
            'pisos' => $pisos,
        ]);
    }

    /**
     * Guardar nuevo departamento
     */
    public function store(Request $request)
    {
        $this->authorize('create', Departamento::class);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        Departamento::query()->create($data);

        return redirect()
            ->route('departamentos.index')
            ->with('message', 'Departamento creado exitosamente.');
    }

    /**
     * Mostrar departamento
     */
    public function show(Departamento $departamento): Response
    {
        $departamento->load(['piso', 'users']);

        return Inertia::render('Departamentos/Show', [
            'departamento' => $departamento,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Departamento $departamento): Response
    {
        $this->authorize('update', $departamento);

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return Inertia::render('Departamentos/Edit', [
            'departamento' => $departamento,
            'pisos' => $pisos,
        ]);
    }

    /**
     * Actualizar departamento
     */
    public function update(Request $request, Departamento $departamento)
    {
        $this->authorize('update', $departamento);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        $departamento->update($data);

        return redirect()
            ->route('departamentos.index')
            ->with('message', 'Departamento actualizado exitosamente.');
    }

    /**
     * Eliminar departamento
     */
    public function destroy(Departamento $departamento)
    {
        // Verificar si hay usuarios asociados
        if ($departamento->users()->count() > 0) {
            return redirect()
                ->route('departamentos.index')
                ->withErrors(['error' => 'No se puede eliminar el departamento porque tiene usuarios asociados.']);
        }

        $departamento->delete();

        return redirect()
            ->route('departamentos.index')
            ->with('message', 'Departamento eliminado exitosamente.');
    }
}
