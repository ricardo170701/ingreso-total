<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePuertaRequest;
use App\Http\Requests\UpdatePuertaRequest;
use App\Models\Material;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\TipoPuerta;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PuertasController extends Controller
{
    /**
     * Listar puertas
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->query('per_page', 12));
        $perPage = max(1, min(100, $perPage));
        $pisoId = $request->query('piso_id');

        $query = Puerta::query()
            ->with(['zona', 'piso', 'tipoPuerta', 'material', 'mantenimientos']);

        // Filtrar por piso si se proporciona
        if ($pisoId) {
            $query->where('piso_id', $pisoId);
        }

        $puertas = $query->orderBy('nombre')->paginate($perPage);

        // Agregar estado de mantenimiento a cada puerta
        $puertas->getCollection()->transform(function ($puerta) {
            $puerta->estado_mantenimiento = $puerta->estado_mantenimiento;
            return $puerta;
        });

        // Cargar todos los pisos para el filtro
        $pisos = Piso::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->withCount('puertas')
            ->get();

        return Inertia::render('Puertas/Index', [
            'puertas' => $puertas,
            'pisos' => $pisos,
            'pisoSeleccionado' => $pisoId ? (int) $pisoId : null,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): Response
    {
        $zonas = Zona::query()->where('activa', true)->orderBy('nombre')->get();
        $pisos = Piso::query()->where('activo', true)->orderBy('orden')->get();
        $tiposPuerta = TipoPuerta::query()->where('activo', true)->orderBy('nombre')->get();
        $materiales = Material::query()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Puertas/Create', [
            'zonas' => $zonas,
            'pisos' => $pisos,
            'tiposPuerta' => $tiposPuerta,
            'materiales' => $materiales,
        ]);
    }

    /**
     * Guardar nueva puerta
     */
    public function store(StorePuertaRequest $request)
    {
        $data = $request->validated();

        // Manejar la subida de imagen
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('puertas', 'public');
        }

        // Valor por defecto para tiempo_apertura si no se proporciona
        if (!isset($data['tiempo_apertura'])) {
            $data['tiempo_apertura'] = 5;
        }

        $puerta = Puerta::query()->create($data);

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta creada exitosamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Puerta $puerta): Response
    {
        $puerta->load(['zona', 'piso', 'tipoPuerta', 'material']);
        $zonas = Zona::query()->where('activa', true)->orderBy('nombre')->get();
        $pisos = Piso::query()->where('activo', true)->orderBy('orden')->get();
        $tiposPuerta = TipoPuerta::query()->where('activo', true)->orderBy('nombre')->get();
        $materiales = Material::query()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Puertas/Edit', [
            'puerta' => $puerta,
            'zonas' => $zonas,
            'pisos' => $pisos,
            'tiposPuerta' => $tiposPuerta,
            'materiales' => $materiales,
        ]);
    }

    /**
     * Actualizar puerta
     */
    public function update(UpdatePuertaRequest $request, Puerta $puerta)
    {
        $data = $request->validated();

        // Manejar la subida de nueva imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($puerta->imagen && Storage::disk('public')->exists($puerta->imagen)) {
                Storage::disk('public')->delete($puerta->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('puertas', 'public');
        }

        $puerta->fill($data);
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
        // Eliminar imagen si existe
        if ($puerta->imagen && Storage::disk('public')->exists($puerta->imagen)) {
            Storage::disk('public')->delete($puerta->imagen);
        }

        $puerta->delete();

        return redirect()
            ->route('puertas.index')
            ->with('message', 'Puerta eliminada exitosamente.');
    }
}
