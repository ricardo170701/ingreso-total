<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpsMantenimientoRequest;
use App\Http\Requests\UpdateUpsMantenimientoRequest;
use App\Models\UpsMantenimientoDocumento;
use App\Models\UpsMantenimientoImagen;
use App\Models\Ups;
use App\Models\UpsMantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;
use ZipStream\ZipStream;

class UpsMantenimientosController extends Controller
{
    private const MAX_FOTOS = 6;
    private const MAX_PDFS = 5;

    public function store(StoreUpsMantenimientoRequest $request, Ups $ups)
    {
        $this->authorize('view', $ups);
        $this->authorize('create', UpsMantenimiento::class);

        $data = $request->validated();

        $fotosCount = $request->hasFile('fotos') ? count($request->file('fotos')) : 0;
        $docsCount = $request->hasFile('documentos') ? count($request->file('documentos')) : 0;
        if ($fotosCount > self::MAX_FOTOS) {
            return back()->withErrors(['fotos' => 'Máximo ' . self::MAX_FOTOS . ' fotos por mantenimiento.'])->withInput();
        }
        if ($docsCount > self::MAX_PDFS) {
            return back()->withErrors(['documentos' => 'Máximo ' . self::MAX_PDFS . ' PDFs por mantenimiento.'])->withInput();
        }

        $mantenimiento = UpsMantenimiento::create([
            'ups_id' => $ups->id,
            'fecha_mantenimiento' => $data['fecha_mantenimiento'],
            'fecha_fin_programada' => ($data['tipo'] ?? 'realizado') === 'programado'
                ? ($data['fecha_fin_programada'] ?? null)
                : null,
            'tipo' => $data['tipo'] ?? 'realizado',
            'descripcion' => $data['descripcion'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // Guardar fotos
        if ($request->hasFile('fotos')) {
            $orden = 0;
            foreach ($request->file('fotos') as $foto) {
                $ruta = $foto->store('ups/mantenimientos/fotos', 'public');
                UpsMantenimientoImagen::create([
                    'ups_mantenimiento_id' => $mantenimiento->id,
                    'ruta_imagen' => $ruta,
                    'orden' => $orden++,
                    'descripcion' => null,
                ]);
            }
        }

        // Guardar documentos PDF
        if ($request->hasFile('documentos')) {
            $orden = 0;
            foreach ($request->file('documentos') as $documento) {
                $ruta = $documento->store('ups/mantenimientos/documentos', 'public');
                UpsMantenimientoDocumento::create([
                    'ups_mantenimiento_id' => $mantenimiento->id,
                    'ruta_documento' => $ruta,
                    'nombre_original' => $documento->getClientOriginalName(),
                    'orden' => $orden++,
                ]);
            }
        }

        return redirect()
            ->route('ups.show', ['ups' => $ups->id])
            ->with('message', 'Mantenimiento de UPS registrado.');
    }

    public function edit(Ups $ups, UpsMantenimiento $mantenimiento): Response
    {
        $this->authorize('view', $ups);
        $this->authorize('update', $mantenimiento);

        if ((int) $mantenimiento->ups_id !== (int) $ups->id) {
            abort(404);
        }

        return Inertia::render('Ups/Mantenimientos/Edit', [
            'ups' => $ups->load('piso'),
            'mantenimiento' => $mantenimiento->load(['creadoPor', 'editadoPor', 'documentos', 'imagenes']),
        ]);
    }

    public function update(UpdateUpsMantenimientoRequest $request, Ups $ups, UpsMantenimiento $mantenimiento)
    {
        $this->authorize('view', $ups);
        $this->authorize('update', $mantenimiento);

        if ((int) $mantenimiento->ups_id !== (int) $ups->id) {
            abort(404);
        }

        $data = $request->validated();

        // Validar límites TOTALES (existentes - eliminados + nuevos)
        $mantenimiento->load(['imagenes', 'documentos']);
        $existFotos = $mantenimiento->imagenes->count();
        $existDocs = $mantenimiento->documentos->count();
        $delFotos = isset($data['imagenes_eliminar']) && is_array($data['imagenes_eliminar']) ? count($data['imagenes_eliminar']) : 0;
        $delDocs = isset($data['documentos_eliminar']) && is_array($data['documentos_eliminar']) ? count($data['documentos_eliminar']) : 0;
        $newFotos = $request->hasFile('fotos') ? count($request->file('fotos')) : 0;
        $newDocs = $request->hasFile('documentos') ? count($request->file('documentos')) : 0;

        $finalFotos = max(0, $existFotos - $delFotos) + $newFotos;
        $finalDocs = max(0, $existDocs - $delDocs) + $newDocs;

        if ($finalFotos > self::MAX_FOTOS) {
            $restantes = max(0, self::MAX_FOTOS - max(0, $existFotos - $delFotos));
            return back()->withErrors([
                'fotos' => "Máximo " . self::MAX_FOTOS . " fotos por mantenimiento. Restantes: {$restantes}.",
            ])->withInput();
        }
        if ($finalDocs > self::MAX_PDFS) {
            $restantes = max(0, self::MAX_PDFS - max(0, $existDocs - $delDocs));
            return back()->withErrors([
                'documentos' => "Máximo " . self::MAX_PDFS . " PDFs por mantenimiento. Restantes: {$restantes}.",
            ])->withInput();
        }

        $mantenimiento->update([
            'fecha_mantenimiento' => $data['fecha_mantenimiento'],
            'fecha_fin_programada' => ($data['tipo'] ?? $mantenimiento->tipo) === 'programado'
                ? ($data['fecha_fin_programada'] ?? $mantenimiento->fecha_fin_programada)
                : null,
            'tipo' => $data['tipo'] ?? $mantenimiento->tipo,
            'descripcion' => $data['descripcion'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        // Eliminar imágenes solicitadas
        if (isset($data['imagenes_eliminar']) && is_array($data['imagenes_eliminar'])) {
            foreach ($data['imagenes_eliminar'] as $imgId) {
                $img = UpsMantenimientoImagen::find($imgId);
                if ($img && (int) $img->ups_mantenimiento_id === (int) $mantenimiento->id) {
                    if ($img->ruta_imagen && Storage::disk('public')->exists($img->ruta_imagen)) {
                        Storage::disk('public')->delete($img->ruta_imagen);
                    }
                    $img->delete();
                }
            }
        }

        // Eliminar documentos solicitados
        if (isset($data['documentos_eliminar']) && is_array($data['documentos_eliminar'])) {
            foreach ($data['documentos_eliminar'] as $docId) {
                $doc = UpsMantenimientoDocumento::find($docId);
                if ($doc && (int) $doc->ups_mantenimiento_id === (int) $mantenimiento->id) {
                    if ($doc->ruta_documento && Storage::disk('public')->exists($doc->ruta_documento)) {
                        Storage::disk('public')->delete($doc->ruta_documento);
                    }
                    $doc->delete();
                }
            }
        }

        // Agregar nuevas fotos
        if ($request->hasFile('fotos')) {
            $ultimoOrden = $mantenimiento->imagenes()->max('orden') ?? -1;
            $orden = $ultimoOrden + 1;
            foreach ($request->file('fotos') as $foto) {
                $ruta = $foto->store('ups/mantenimientos/fotos', 'public');
                UpsMantenimientoImagen::create([
                    'ups_mantenimiento_id' => $mantenimiento->id,
                    'ruta_imagen' => $ruta,
                    'orden' => $orden++,
                    'descripcion' => null,
                ]);
            }
        }

        // Agregar nuevos documentos
        if ($request->hasFile('documentos')) {
            $ultimoOrden = $mantenimiento->documentos()->max('orden') ?? -1;
            $orden = $ultimoOrden + 1;
            foreach ($request->file('documentos') as $documento) {
                $ruta = $documento->store('ups/mantenimientos/documentos', 'public');
                UpsMantenimientoDocumento::create([
                    'ups_mantenimiento_id' => $mantenimiento->id,
                    'ruta_documento' => $ruta,
                    'nombre_original' => $documento->getClientOriginalName(),
                    'orden' => $orden++,
                ]);
            }
        }

        return redirect()
            ->route('ups.show', ['ups' => $ups->id])
            ->with('message', 'Mantenimiento de UPS actualizado.');
    }

    public function destroy(Ups $ups, UpsMantenimiento $mantenimiento)
    {
        $this->authorize('view', $ups);
        $this->authorize('delete', $mantenimiento);

        if ((int) $mantenimiento->ups_id !== (int) $ups->id) {
            abort(404);
        }

        $mantenimiento->load(['documentos', 'imagenes']);
        foreach ($mantenimiento->documentos as $doc) {
            if ($doc->ruta_documento && Storage::disk('public')->exists($doc->ruta_documento)) {
                Storage::disk('public')->delete($doc->ruta_documento);
            }
        }
        foreach ($mantenimiento->imagenes as $img) {
            if ($img->ruta_imagen && Storage::disk('public')->exists($img->ruta_imagen)) {
                Storage::disk('public')->delete($img->ruta_imagen);
            }
        }

        $mantenimiento->delete();

        return redirect()
            ->route('ups.show', ['ups' => $ups->id])
            ->with('message', 'Mantenimiento de UPS eliminado.');
    }

    /**
     * Marcar mantenimiento programado como completado
     */
    public function marcarCompletado(Request $request, Ups $ups, UpsMantenimiento $mantenimiento)
    {
        $this->authorize('view', $ups);
        $this->authorize('update', $mantenimiento);

        if ((int) $mantenimiento->ups_id !== (int) $ups->id) {
            abort(404);
        }

        if ($mantenimiento->tipo !== 'programado') {
            return back()->withErrors([
                'tipo' => 'Solo se pueden completar mantenimientos programados.',
            ]);
        }

        $mantenimiento->update([
            'tipo' => 'realizado',
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('ups.show', ['ups' => $ups->id])
            ->with('message', 'Mantenimiento marcado como completado.');
    }

    public function downloadZip(Request $request, Ups $ups, UpsMantenimiento $mantenimiento)
    {
        $this->authorize('view', $ups);
        $this->authorize('view', $mantenimiento);

        if ((int) $mantenimiento->ups_id !== (int) $ups->id) {
            abort(404);
        }

        $mantenimiento->load(['imagenes', 'documentos']);

        [$zipName, $rootFolder] = $this->buildZipMeta($ups, $mantenimiento);

        // Verificar disponibilidad de ZipArchive (extensión PHP zip)
        $useZipArchive = extension_loaded('zip') && class_exists(ZipArchive::class);

        if ($useZipArchive) {
            return $this->downloadZipWithZipArchive($mantenimiento, $zipName, $rootFolder);
        } else {
            // Usar ZipStream como alternativa (no requiere extensión PHP zip)
            return $this->downloadZipWithZipStream($mantenimiento, $zipName, $rootFolder);
        }
    }

    /**
     * Genera ZIP usando ZipArchive (requiere extensión PHP zip)
     */
    private function downloadZipWithZipArchive(UpsMantenimiento $mantenimiento, string $zipName, string $rootFolder)
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }

        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No se pudo crear el archivo ZIP.');
        }

        // Fotos
        foreach ($mantenimiento->imagenes->sortBy('orden')->values() as $i => $img) {
            $rel = $img->ruta_imagen;
            if (!$rel || !Storage::disk('public')->exists($rel)) {
                continue;
            }
            $abs = storage_path('app/public/' . $rel);
            if (!is_file($abs)) {
                continue;
            }
            $ext = pathinfo($rel, PATHINFO_EXTENSION) ?: 'jpg';
            $n = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $zip->addFile($abs, "{$rootFolder}/fotos/{$n}.{$ext}");
        }

        // PDFs
        foreach ($mantenimiento->documentos->sortBy('orden')->values() as $i => $doc) {
            $rel = $doc->ruta_documento;
            if (!$rel || !Storage::disk('public')->exists($rel)) {
                continue;
            }
            $abs = storage_path('app/public/' . $rel);
            if (!is_file($abs)) {
                continue;
            }
            $base = $doc->nombre_original ?: ("documento_" . ($i + 1) . ".pdf");
            $safe = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '_', $base) ?: ("documento_" . ($i + 1) . ".pdf");
            if (!str_ends_with(strtolower($safe), '.pdf')) {
                $safe .= '.pdf';
            }
            $n = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $zip->addFile($abs, "{$rootFolder}/pdfs/{$n}_{$safe}");
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Genera ZIP usando ZipStream (no requiere extensión PHP zip)
     */
    private function downloadZipWithZipStream(UpsMantenimiento $mantenimiento, string $zipName, string $rootFolder)
    {
        return response()->streamDownload(function () use ($mantenimiento, $zipName, $rootFolder) {
            // ZipStream 3.x: usar php://output como stream y desactivar headers HTTP
            // Laravel maneja los headers a través de response()->streamDownload()
            $outputStream = fopen('php://output', 'w');
            if (!$outputStream) {
                abort(500, 'No se pudo abrir el stream de salida.');
            }

            $zip = new ZipStream(
                outputStream: $outputStream,
                sendHttpHeaders: false, // Laravel maneja los headers
                outputName: $zipName,
            );

            // Fotos
            foreach ($mantenimiento->imagenes->sortBy('orden')->values() as $i => $img) {
                $rel = $img->ruta_imagen;
                if (!$rel || !Storage::disk('public')->exists($rel)) {
                    continue;
                }
                $abs = storage_path('app/public/' . $rel);
                if (!is_file($abs)) {
                    continue;
                }
                $ext = pathinfo($rel, PATHINFO_EXTENSION) ?: 'jpg';
                $n = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
                $zip->addFileFromPath(
                    fileName: "{$rootFolder}/fotos/{$n}.{$ext}",
                    path: $abs
                );
            }

            // PDFs
            foreach ($mantenimiento->documentos->sortBy('orden')->values() as $i => $doc) {
                $rel = $doc->ruta_documento;
                if (!$rel || !Storage::disk('public')->exists($rel)) {
                    continue;
                }
                $abs = storage_path('app/public/' . $rel);
                if (!is_file($abs)) {
                    continue;
                }
                $base = $doc->nombre_original ?: ("documento_" . ($i + 1) . ".pdf");
                $safe = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '_', $base) ?: ("documento_" . ($i + 1) . ".pdf");
                if (!str_ends_with(strtolower($safe), '.pdf')) {
                    $safe .= '.pdf';
                }
                $n = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
                $zip->addFileFromPath(
                    fileName: "{$rootFolder}/pdfs/{$n}_{$safe}",
                    path: $abs
                );
            }

            $zip->finish();
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Construye nombre del ZIP y carpeta raíz dentro del ZIP (por fecha/orden).
     *
     * @return array{0:string,1:string} [$zipName, $rootFolder]
     */
    private function buildZipMeta(Ups $ups, UpsMantenimiento $mantenimiento): array
    {
        $codigo = (string) ($ups->codigo ?: ('ups_' . $ups->id));
        $codigoSafe = preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo) ?: ('ups_' . $ups->id);

        $fecha = $mantenimiento->fecha_mantenimiento?->format('Y-m-d') ?? now()->format('Y-m-d');

        // Nombre amigable (sin caracteres problemáticos para Windows)
        $zipName = "ups-{$codigoSafe}-mto-{$mantenimiento->id}-{$fecha}.zip";

        // Estructura dentro del ZIP: carpeta raíz por fecha/ID para organizar adjuntos
        $rootFolder = "ups_{$codigoSafe}/{$fecha}_mto_{$mantenimiento->id}";

        return [$zipName, $rootFolder];
    }
}
