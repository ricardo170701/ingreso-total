<?php

namespace App\Http\Controllers;

use App\Models\Secretaria;
use App\Models\Piso;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecretariasController extends Controller
{
    /**
     * Listar secretarías (ya no se listan por dependencia)
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Secretaria::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $secretarias = Secretaria::query()
            ->with('piso')
            ->withCount('gerencias')
            ->orderBy('nombre')
            ->paginate($perPage);

        return Inertia::render('Secretarias/Index', [
            'secretarias' => $secretarias,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $this->authorize('create', Secretaria::class);

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return Inertia::render('Secretarias/Create', [
            'pisos' => $pisos,
        ]);
    }

    /**
     * Guardar nueva secretaría
     */
    public function store(Request $request)
    {
        $this->authorize('create', Secretaria::class);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        Secretaria::query()->create($data);

        return redirect()
            ->route('dependencias.index')
            ->with('message', 'Secretaría creada exitosamente.');
    }

    /**
     * Mostrar secretaría con sus gerencias
     */
    public function show(Secretaria $secretaria): Response
    {
        $secretaria->load([
            'piso',
            'gerencias' => function ($query) {
                $query->withCount('users')->orderBy('nombre');
            }
        ]);

        return Inertia::render('Secretarias/Show', [
            'secretaria' => $secretaria,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Secretaria $secretaria): Response
    {
        $this->authorize('update', $secretaria);

        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return Inertia::render('Secretarias/Edit', [
            'secretaria' => $secretaria,
            'pisos' => $pisos,
        ]);
    }

    /**
     * Actualizar secretaría
     */
    public function update(Request $request, Secretaria $secretaria)
    {
        $this->authorize('update', $secretaria);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        $secretaria->update($data);

        return redirect()
            ->route('secretarias.show', $secretaria)
            ->with('message', 'Secretaría actualizada exitosamente.');
    }

    /**
     * Eliminar secretaría
     */
    public function destroy(Secretaria $secretaria)
    {
        // Verificar si hay gerencias con usuarios asociados
        $totalUsuarios = $secretaria->gerencias()->withCount('users')->get()->sum('users_count');
        
        if ($totalUsuarios > 0) {
            return redirect()
                ->route('dependencias.index')
                ->withErrors(['error' => 'No se puede eliminar la secretaría porque tiene usuarios asociados a través de sus gerencias.']);
        }

        $secretaria->delete();

        return redirect()
            ->route('dependencias.index')
            ->with('message', 'Secretaría eliminada exitosamente.');
    }
}