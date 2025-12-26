<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMantenimientoRequest;
use App\Http\Requests\UpdateMantenimientoRequest;
use App\Models\Defecto;
use App\Models\Mantenimiento;
use App\Models\MantenimientoImagen;
use App\Models\Puerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MantenimientosController extends Controller
{
    /**
     * Listar mantenimientos
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));
        $puertaId = $request->query('puerta_id');

        $query = Mantenimiento::query()
            ->with(['puerta.piso', 'usuario', 'defectos', 'imagenes']);

        // Filtrar por puerta si se proporciona
        if ($puertaId) {
            $query->where('puerta_id', $puertaId);
        }

        $mantenimientos = $query->orderBy('fecha_mantenimiento', 'desc')->paginate($perPage);

        return Inertia::render('Mantenimientos/Index', [
            'mantenimientos' => $mantenimientos,
            'puertaFiltrada' => $puertaId ? (int) $puertaId : null,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request): Response
    {
        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get();
        $defectos = Defecto::query()->where('activo', true)->orderBy('nombre')->get();
        $puertaId = $request->query('puerta_id');

        return Inertia::render('Mantenimientos/Create', [
            'puertas' => $puertas,
            'defectos' => $defectos,
            'puertaSeleccionada' => $puertaId ? (int) $puertaId : null,
        ]);
    }

    /**
     * Guardar nuevo mantenimiento
     */
    public function store(StoreMantenimientoRequest $request)
    {
        $data = $request->validated();

        // Crear el mantenimiento
        $mantenimiento = Mantenimiento::create([
            'puerta_id' => $data['puerta_id'],
            'usuario_id' => $request->user()->id,
            'fecha_mantenimiento' => $data['fecha_mantenimiento'],
            'tipo' => $data['tipo'] ?? 'realizado',
            'fecha_fin_programada' => $data['fecha_fin_programada'] ?? null,
            'otros_defectos' => $data['otros_defectos'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        // Asociar defectos con sus niveles de gravedad
        $defectosConNivel = [];
        foreach ($data['defectos'] as $defecto) {
            $defectosConNivel[$defecto['id']] = ['nivel_gravedad' => $defecto['nivel_gravedad']];
        }
        $mantenimiento->defectos()->attach($defectosConNivel);

        // Guardar imágenes
        if ($request->hasFile('imagenes')) {
            $orden = 0;
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('mantenimientos', 'public');
                MantenimientoImagen::create([
                    'mantenimiento_id' => $mantenimiento->id,
                    'ruta_imagen' => $ruta,
                    'orden' => $orden++,
                ]);
            }
        }

        return redirect()
            ->route('mantenimientos.index')
            ->with('message', 'Mantenimiento registrado exitosamente.');
    }

    /**
     * Mostrar mantenimiento
     */
    public function show(Mantenimiento $mantenimiento): Response
    {
        $mantenimiento->load(['puerta.piso', 'usuario', 'defectos', 'imagenes']);

        return Inertia::render('Mantenimientos/Show', [
            'mantenimiento' => $mantenimiento,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Mantenimiento $mantenimiento): Response
    {
        $mantenimiento->load(['puerta.piso', 'usuario', 'defectos', 'imagenes']);
        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get();
        $defectos = Defecto::query()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Mantenimientos/Edit', [
            'mantenimiento' => $mantenimiento,
            'puertas' => $puertas,
            'defectos' => $defectos,
        ]);
    }

    /**
     * Actualizar mantenimiento
     */
    public function update(UpdateMantenimientoRequest $request, Mantenimiento $mantenimiento)
    {
        $data = $request->validated();

        // Actualizar datos básicos
        $mantenimiento->update([
            'puerta_id' => $data['puerta_id'] ?? $mantenimiento->puerta_id,
            'fecha_mantenimiento' => $data['fecha_mantenimiento'] ?? $mantenimiento->fecha_mantenimiento,
            'tipo' => $data['tipo'] ?? $mantenimiento->tipo,
            'fecha_fin_programada' => $data['fecha_fin_programada'] ?? $mantenimiento->fecha_fin_programada,
            'otros_defectos' => $data['otros_defectos'] ?? $mantenimiento->otros_defectos,
            'observaciones' => $data['observaciones'] ?? $mantenimiento->observaciones,
        ]);

        // Actualizar defectos si se proporcionan
        if (isset($data['defectos'])) {
            $defectosConNivel = [];
            foreach ($data['defectos'] as $defecto) {
                $defectosConNivel[$defecto['id']] = ['nivel_gravedad' => $defecto['nivel_gravedad']];
            }
            $mantenimiento->defectos()->sync($defectosConNivel);
        }

        // Agregar nuevas imágenes si se proporcionan
        if ($request->hasFile('imagenes')) {
            $ultimoOrden = $mantenimiento->imagenes()->max('orden') ?? -1;
            $orden = $ultimoOrden + 1;

            // Verificar que no exceda 10 imágenes en total
            $totalImagenes = $mantenimiento->imagenes()->count();
            $nuevasImagenes = count($request->file('imagenes'));

            if ($totalImagenes + $nuevasImagenes > 10) {
                return back()->withErrors([
                    'imagenes' => 'No se pueden agregar más de 10 imágenes en total.',
                ]);
            }

            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('mantenimientos', 'public');
                MantenimientoImagen::create([
                    'mantenimiento_id' => $mantenimiento->id,
                    'ruta_imagen' => $ruta,
                    'orden' => $orden++,
                ]);
            }
        }

        return redirect()
            ->route('mantenimientos.index')
            ->with('message', 'Mantenimiento actualizado exitosamente.');
    }

    /**
     * Eliminar mantenimiento
     */
    public function destroy(Mantenimiento $mantenimiento)
    {
        // Eliminar imágenes asociadas
        foreach ($mantenimiento->imagenes as $imagen) {
            if (Storage::disk('public')->exists($imagen->ruta_imagen)) {
                Storage::disk('public')->delete($imagen->ruta_imagen);
            }
        }

        $mantenimiento->delete();

        return redirect()
            ->route('mantenimientos.index')
            ->with('message', 'Mantenimiento eliminado exitosamente.');
    }

    /**
     * Eliminar una imagen específica
     */
    public function eliminarImagen(MantenimientoImagen $imagen)
    {
        if (Storage::disk('public')->exists($imagen->ruta_imagen)) {
            Storage::disk('public')->delete($imagen->ruta_imagen);
        }

        $imagen->delete();

        return back()->with('message', 'Imagen eliminada exitosamente.');
    }
}
