<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMantenimientoRequest;
use App\Http\Requests\UpdateMantenimientoRequest;
use App\Models\Mantenimiento;
use App\Models\MantenimientoDocumento;
use App\Models\Puerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class MantenimientosController extends Controller
{
    /**
     * Listar mantenimientos
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Mantenimiento::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));
        $puertaId = $request->query('puerta_id');
        $tipo = $request->query('tipo'); // 'realizado' o 'programado'

        $query = Mantenimiento::query()
            ->with(['puerta.piso', 'documentos', 'creadoPor', 'editadoPor']);

        // Filtrar por puerta si se proporciona
        if ($puertaId) {
            $query->where('puerta_id', $puertaId);
        }

        // Filtrar por tipo/estado si se proporciona
        if ($tipo && in_array($tipo, ['realizado', 'programado'])) {
            $query->where('tipo', $tipo);
        }

        $mantenimientos = $query->orderBy('fecha_mantenimiento', 'desc')->paginate($perPage);

        // Obtener todas las puertas para el filtro
        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Mantenimientos/Index', [
            'mantenimientos' => $mantenimientos,
            'puertas' => $puertas,
            'filtros' => [
                'puerta_id' => $puertaId ? (int) $puertaId : null,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Mantenimiento::class);

        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get();
        $puertaId = $request->query('puerta_id');

        return Inertia::render('Mantenimientos/Create', [
            'puertas' => $puertas,
            'puertaSeleccionada' => $puertaId ? (int) $puertaId : null,
        ]);
    }

    /**
     * Guardar nuevo mantenimiento
     */
    public function store(StoreMantenimientoRequest $request)
    {
        $this->authorize('create', Mantenimiento::class);

        $data = $request->validated();

        // Crear el mantenimiento
        $mantenimiento = Mantenimiento::create([
            'puerta_id' => $data['puerta_id'],
            'fecha_mantenimiento' => $data['fecha_mantenimiento'],
            'fecha_fin_programada' => ($data['tipo'] ?? 'realizado') === 'programado'
                ? ($data['fecha_fin_programada'] ?? null)
                : null,
            'tipo' => $data['tipo'] ?? 'realizado',
            'descripcion_mantenimiento' => $data['descripcion_mantenimiento'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // Guardar documentos (PDFs)
        if ($request->hasFile('documentos')) {
            $orden = 0;
            foreach ($request->file('documentos') as $documento) {
                $ruta = $documento->store('mantenimientos/documentos', 'public');
                MantenimientoDocumento::create([
                    'mantenimiento_id' => $mantenimiento->id,
                    'ruta_documento' => $ruta,
                    'nombre_original' => $documento->getClientOriginalName(),
                    'orden' => $orden++,
                ]);
            }
        }

        // Si viene desde una puerta, redirigir a la puerta, si no, a mantenimientos.index
        $redirectToPuertaId = $request->input('redirect_to_puerta_id');
        if ($redirectToPuertaId) {
            return redirect()
                ->route('puertas.show', ['puerta' => $redirectToPuertaId])
                ->with('message', 'Mantenimiento registrado exitosamente.');
        }

        return redirect()
            ->route('mantenimientos.index')
            ->with('message', 'Mantenimiento registrado exitosamente.');
    }

    /**
     * Mostrar mantenimiento
     */
    public function show(Request $request, Mantenimiento $mantenimiento): Response
    {
        $this->authorize('view', $mantenimiento);

        $mantenimiento->load(['puerta.piso', 'documentos', 'creadoPor', 'editadoPor']);
        $fromPuertaId = $request->query('from_puerta_id');

        return Inertia::render('Mantenimientos/Show', [
            'mantenimiento' => $mantenimiento,
            'fromPuertaId' => $fromPuertaId ? (int) $fromPuertaId : null,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Request $request, Mantenimiento $mantenimiento): Response
    {
        $this->authorize('update', $mantenimiento);

        $mantenimiento->load(['puerta.piso', 'documentos', 'creadoPor', 'editadoPor']);
        $puertas = Puerta::query()
            ->where('activo', true)
            ->with('piso')
            ->orderBy('nombre')
            ->get();
        $fromPuertaId = $request->query('from_puerta_id');

        return Inertia::render('Mantenimientos/Edit', [
            'mantenimiento' => $mantenimiento,
            'puertas' => $puertas,
            'fromPuertaId' => $fromPuertaId ? (int) $fromPuertaId : null,
        ]);
    }

    /**
     * Actualizar mantenimiento
     */
    public function update(UpdateMantenimientoRequest $request, Mantenimiento $mantenimiento)
    {
        $this->authorize('update', $mantenimiento);

        $data = $request->validated();

        // Actualizar datos básicos
        $mantenimiento->update([
            'puerta_id' => $data['puerta_id'] ?? $mantenimiento->puerta_id,
            'fecha_mantenimiento' => $data['fecha_mantenimiento'] ?? $mantenimiento->fecha_mantenimiento,
            'fecha_fin_programada' => ($data['tipo'] ?? $mantenimiento->tipo) === 'programado'
                ? ($data['fecha_fin_programada'] ?? $mantenimiento->fecha_fin_programada)
                : null,
            'tipo' => $data['tipo'] ?? $mantenimiento->tipo,
            'falla' => $data['falla'] ?? $mantenimiento->falla,
            'updated_by' => $request->user()->id,
        ]);

        // Eliminar documentos solicitados
        if (isset($data['documentos_eliminar']) && is_array($data['documentos_eliminar'])) {
            foreach ($data['documentos_eliminar'] as $documentoId) {
                $documento = MantenimientoDocumento::find($documentoId);
                if ($documento && $documento->mantenimiento_id === $mantenimiento->id) {
                    if (Storage::disk('public')->exists($documento->ruta_documento)) {
                        Storage::disk('public')->delete($documento->ruta_documento);
                    }
                    $documento->delete();
                }
            }
        }

        // Agregar nuevos documentos si se proporcionan
        if ($request->hasFile('documentos')) {
            $totalDocumentos = $mantenimiento->documentos()->count();
            $nuevosDocumentos = count($request->file('documentos'));

            // Verificar que no exceda 5 documentos en total
            if ($totalDocumentos + $nuevosDocumentos > 5) {
                return back()->withErrors([
                    'documentos' => 'No se pueden agregar más de 5 documentos en total.',
                ]);
            }

            $ultimoOrden = $mantenimiento->documentos()->max('orden') ?? -1;
            $orden = $ultimoOrden + 1;

            foreach ($request->file('documentos') as $documento) {
                $ruta = $documento->store('mantenimientos/documentos', 'public');
                MantenimientoDocumento::create([
                    'mantenimiento_id' => $mantenimiento->id,
                    'ruta_documento' => $ruta,
                    'nombre_original' => $documento->getClientOriginalName(),
                    'orden' => $orden++,
                ]);
            }
        }

        // Si viene desde una puerta, redirigir a la puerta, si no, a mantenimientos.show
        $fromPuertaId = $request->input('from_puerta_id');
        if ($fromPuertaId) {
            return redirect()
                ->route('puertas.show', ['puerta' => $fromPuertaId])
                ->with('message', 'Mantenimiento actualizado exitosamente.');
        }

        return redirect()
            ->route('mantenimientos.show', $mantenimiento)
            ->with('message', 'Mantenimiento actualizado exitosamente.');
    }

    /**
     * Eliminar mantenimiento
     */
    public function destroy(Mantenimiento $mantenimiento)
    {
        $this->authorize('delete', $mantenimiento);

        // Eliminar documentos asociados
        foreach ($mantenimiento->documentos as $documento) {
            if (Storage::disk('public')->exists($documento->ruta_documento)) {
                Storage::disk('public')->delete($documento->ruta_documento);
            }
        }

        $mantenimiento->delete();

        return redirect()
            ->route('mantenimientos.index')
            ->with('message', 'Mantenimiento eliminado exitosamente.');
    }

    /**
     * Marcar mantenimiento programado como completado
     */
    public function marcarCompletado(Request $request, Mantenimiento $mantenimiento)
    {
        $this->authorize('update', $mantenimiento);

        if ($mantenimiento->tipo !== 'programado') {
            return back()->withErrors([
                'tipo' => 'Solo se pueden completar mantenimientos programados.',
            ]);
        }

        $mantenimiento->update([
            'tipo' => 'realizado',
            'updated_by' => $request->user()->id,
        ]);

        // Si viene desde una puerta, redirigir a la puerta, si no, a mantenimientos.index
        $fromPuertaId = $request->input('from_puerta_id');
        if ($fromPuertaId) {
            return redirect()
                ->route('puertas.show', ['puerta' => $fromPuertaId])
                ->with('message', 'Mantenimiento marcado como completado exitosamente.');
        }

        return redirect()
            ->route('mantenimientos.index')
            ->with('message', 'Mantenimiento marcado como completado exitosamente.');
    }

    /**
     * Descargar PDF del mantenimiento
     */
    public function downloadPdf(Mantenimiento $mantenimiento)
    {
        $this->authorize('downloadPdf', $mantenimiento);

        // Cargar todas las relaciones necesarias
        $mantenimiento->load(['puerta.piso', 'documentos', 'creadoPor', 'editadoPor']);

        // Generar PDF
        $pdf = Pdf::loadView('mantenimientos.pdf', [
            'mantenimiento' => $mantenimiento,
        ]);

        // Nombre del archivo
        $filename = 'mantenimiento_' . $mantenimiento->id . '_' . date('Y-m-d') . '.pdf';

        // Retornar descarga
        return $pdf->download($filename);
    }
}
