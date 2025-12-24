<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePuertaRequest;
use App\Http\Requests\UpdatePuertaRequest;
use App\Models\Puerta;
use App\Models\Zona;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PuertasController extends Controller
{
    /**
     * Listar puertas
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $puertas = Puerta::query()
            ->with('zona')
            ->orderBy('id')
            ->paginate($perPage);

        return Inertia::render('Puertas/Index', [
            'puertas' => $puertas,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $zonas = Zona::query()->where('activa', true)->orderBy('nombre')->get();

        return Inertia::render('Puertas/Create', [
            'zonas' => $zonas,
        ]);
    }

    /**
     * Guardar nueva puerta
     */
    public function store(StorePuertaRequest $request)
    {
        $puerta = Puerta::query()->create($request->validated());

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta creada exitosamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Puerta $puerta): Response
    {
        $puerta->load('zona');
        $zonas = Zona::query()->where('activa', true)->orderBy('nombre')->get();

        return Inertia::render('Puertas/Edit', [
            'puerta' => $puerta,
            'zonas' => $zonas,
        ]);
    }

    /**
     * Actualizar puerta
     */
    public function update(UpdatePuertaRequest $request, Puerta $puerta)
    {
        $puerta->fill($request->validated());
        $puerta->save();

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta actualizada exitosamente.');
    }

    /**
     * Eliminar puerta
     */
    public function destroy(Puerta $puerta)
    {
        $puerta->delete();

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta eliminada exitosamente.');
    }
}
