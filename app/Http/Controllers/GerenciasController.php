<?php

namespace App\Http\Controllers;

use App\Models\Gerencia;
use App\Models\Secretaria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GerenciasController extends Controller
{
    /**
     * Listar gerencias de una secretaría
     */
    public function index(Request $request, Secretaria $secretaria): Response
    {
        $this->authorize('viewAny', Gerencia::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $gerencias = Gerencia::query()
            ->where('secretaria_id', $secretaria->id)
            ->with('secretaria.piso')
            ->withCount('users')
            ->orderBy('nombre')
            ->paginate($perPage);

        return Inertia::render('Gerencias/Index', [
            'secretaria' => $secretaria,
            'gerencias' => $gerencias,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Secretaria $secretaria): Response
    {
        $this->authorize('create', Gerencia::class);

        return Inertia::render('Gerencias/Create', [
            'secretaria' => $secretaria,
        ]);
    }

    /**
     * Guardar nueva gerencia
     * El nombre debe ser único dentro de la secretaría
     */
    public function store(Request $request, Secretaria $secretaria)
    {
        $this->authorize('create', Gerencia::class);

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                // Validar que el nombre sea único dentro de la secretaría
                function ($attribute, $value, $fail) use ($secretaria) {
                    $exists = Gerencia::query()
                        ->where('secretaria_id', $secretaria->id)
                        ->where('nombre', $value)
                        ->exists();
                    
                    if ($exists) {
                        $fail('El nombre de la gerencia ya existe en esta secretaría.');
                    }
                },
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        $data['secretaria_id'] = $secretaria->id;
        Gerencia::query()->create($data);

        return redirect()
            ->route('secretarias.show', $secretaria)
            ->with('message', 'Gerencia creada exitosamente.');
    }

    /**
     * Mostrar gerencia con sus usuarios
     */
    public function show(Secretaria $secretaria, Gerencia $gerencia): Response
    {
        // Validar que la gerencia pertenece a la secretaría
        if ($gerencia->secretaria_id !== $secretaria->id) {
            abort(404);
        }

        $gerencia->load([
            'secretaria.piso',
            'users' => function ($query) {
                $query->with(['role', 'cargo'])->orderBy('nombre');
            }
        ]);

        return Inertia::render('Gerencias/Show', [
            'secretaria' => $secretaria,
            'gerencia' => $gerencia,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Secretaria $secretaria, Gerencia $gerencia): Response
    {
        // Validar que la gerencia pertenece a la secretaría
        if ($gerencia->secretaria_id !== $secretaria->id) {
            abort(404);
        }

        $this->authorize('update', $gerencia);

        return Inertia::render('Gerencias/Edit', [
            'secretaria' => $secretaria,
            'gerencia' => $gerencia,
        ]);
    }

    /**
     * Actualizar gerencia
     * El nombre debe ser único dentro de la secretaría (excepto para la misma gerencia)
     */
    public function update(Request $request, Secretaria $secretaria, Gerencia $gerencia)
    {
        // Validar que la gerencia pertenece a la secretaría
        if ($gerencia->secretaria_id !== $secretaria->id) {
            abort(404);
        }

        $this->authorize('update', $gerencia);

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                // Validar que el nombre sea único dentro de la secretaría, excluyendo la gerencia actual
                function ($attribute, $value, $fail) use ($secretaria, $gerencia) {
                    $exists = Gerencia::query()
                        ->where('secretaria_id', $secretaria->id)
                        ->where('nombre', $value)
                        ->where('id', '!=', $gerencia->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('El nombre de la gerencia ya existe en esta secretaría.');
                    }
                },
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        $gerencia->update($data);

        return redirect()
            ->route('secretarias.show', $secretaria)
            ->with('message', 'Gerencia actualizada exitosamente.');
    }

    /**
     * Eliminar gerencia
     */
    public function destroy(Secretaria $secretaria, Gerencia $gerencia)
    {
        // Validar que la gerencia pertenece a la secretaría
        if ($gerencia->secretaria_id !== $secretaria->id) {
            abort(404);
        }

        // Verificar si hay usuarios asociados
        if ($gerencia->users()->count() > 0) {
            return redirect()
                ->route('secretarias.show', $secretaria)
                ->withErrors(['error' => 'No se puede eliminar la gerencia porque tiene usuarios asociados.']);
        }

        $gerencia->delete();

        return redirect()
            ->route('secretarias.show', $secretaria)
            ->with('message', 'Gerencia eliminada exitosamente.');
    }
}