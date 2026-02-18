<?php

namespace App\Http\Controllers;

use App\Models\Ups;
use App\Models\UpsBitacora;
use App\Models\UpsBitacoraImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use OpenAI\Laravel\Facades\OpenAI;
use ZipArchive;
use ZipStream\ZipStream;
use Barryvdh\DomPDF\Facade\Pdf;

class UpsBitacoraController extends Controller
{
    public function index(Request $request, Ups $ups): Response
    {
        $this->authorize('view', $ups);

        $query = $ups->bitacora()
            ->with(['creadoPor', 'imagenes']);

        // Filtros por fecha
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        $bitacora = $query->orderBy('created_at', 'desc')
            ->paginate(8)
            ->withQueryString();

        return Inertia::render('Ups/Bitacora/Index', [
            'ups' => $ups,
            'bitacora' => $bitacora,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ],
        ]);
    }

    public function create(Ups $ups): Response
    {
        $this->authorize('view', $ups);

        return Inertia::render('Ups/Bitacora/Create', [
            'ups' => $ups,
        ]);
    }

    public function analyzeImage(Request $request, Ups $ups)
    {
        $this->authorize('view', $ups);

        $request->validate([
            'imagenes' => 'required|array|min:1|max:5',
            'imagenes.*' => 'required|image|mimes:jpeg,jpg,png|max:10240', // 10MB max por imagen
        ]);

        $imagenesPaths = [];
        $imagenesData = [];

        try {
            $datosCombinados = [
                'indicadores' => ['normal' => false, 'battery' => false, 'bypass' => false, 'fault' => false],
                'input' => ['voltage' => null, 'frequency' => null],
                'output' => ['voltage' => null, 'frequency' => null, 'power' => null],
                'battery' => [
                    'voltage' => null,
                    'percentage' => null,
                    'tiempo_respaldo_min' => null,
                    'tiempo_descarga_min' => null,
                    'estado' => null,
                ],
                'temperatura' => null,
                'fases' => ['a' => null, 'b' => null, 'c' => null],
                'alarmas' => [],
                'colores_indicadores' => [],
                'modelo_ups' => null,
                'datos_adicionales' => [],
            ];

            // Preparar prompt mejorado para GPT-4 Vision
            $prompt = "Analiza esta imagen del panel frontal de un UPS (Uninterruptible Power Supply). Puede ser de diferentes modelos (POWEST, APC, Eaton, etc.) que muestran los datos de forma diferente.

IMPORTANTE: Reconoce estos 3 formatos comunes:
1. Panel con indicadores NORMAL/BATTERY/BYPASS/FAULT y secciones Input/Output/Battery
2. Pantalla POWEST con Modo Unitario, datos de batería, voltajes de fase (A, B, C), corrientes
3. Pantalla con alarmas, estados, temperaturas, y otros datos operativos

Debes identificar y extraer TODOS los datos visibles:

1. INDICADORES DE ESTADO (luces/leds):
   - NORMAL (verde) - si está encendido (true) o apagado (false)
   - BATTERY (amarillo/amarillo-verde) - si está encendido (true) o apagado (false)
   - BYPASS (amarillo) - si está encendido (true) o apagado (false)
   - FAULT (rojo) - si está encendido (true) o apagado (false)
   - COLORES: indica el color exacto de cada indicador (verde, amarillo, rojo, gris, apagado, etc.)

2. INPUT (entrada):
   - Voltaje en V (puede estar como \"Input\", \"Entrada\", \"Voltaje De Fase\", \"Línea Volt\")
   - Frecuencia en Hz

3. OUTPUT (salida):
   - Voltaje en V (puede estar como \"Output\", \"Salida\", \"Voltaje De Fase\", \"Línea Volt\")
   - Frecuencia en Hz
   - Potencia en W o VA

4. BATTERY (batería):
   - Voltaje en V (puede estar como \"Battery Volt\", \"Voltaje Batería\")
   - Porcentaje en % (puede estar como \"Battery level\", \"Cap. En Baterias\", \"Battery Capacity\")
   - Tiempo de respaldo en minutos (si está visible)
   - Tiempo de descarga en minutos (si está visible)
   - Estado: \"charging\", \"discharging\", \"standby\" (si está visible)

5. FASES (si el UPS es trifásico):
   - Fase A: voltaje, corriente, frecuencia
   - Fase B: voltaje, corriente, frecuencia
   - Fase C: voltaje, corriente, frecuencia

6. ALARMAS Y ESTADOS:
   - Códigos de alarma (ej: \"AL*129\", \"CPY - EA warn\")
   - Mensajes de estado (ej: \"No active alarms\", \"Battery charging\", \"STATUS: Cooling\")
   - Fechas y horas de alarmas

7. TEMPERATURA:
   - Temperatura ambiente o interna del UPS en °C (si está visible)
   - Puede estar como \"Temp\", \"Temperature\", \"°C\", o similar
   - Busca valores numéricos seguidos de \"°C\" o \"C\"

8. DATOS ADICIONALES:
   - Modelo/marca del UPS (ej: \"POWEST\", \"30 kVA\", \"APC\")
   - Modo operativo (ej: \"Modo Unitario\", \"Modo Principal\")
   - Cualquier otro dato numérico o texto relevante

Responde SOLO con un JSON válido en este formato exacto:
{
  \"indicadores\": {
    \"normal\": true/false,
    \"battery\": true/false,
    \"bypass\": true/false,
    \"fault\": true/false
  },
  \"colores_indicadores\": {
    \"normal\": \"verde\" o \"apagado\" o null,
    \"battery\": \"amarillo\" o \"verde\" o \"apagado\" o null,
    \"bypass\": \"amarillo\" o \"apagado\" o null,
    \"fault\": \"rojo\" o \"apagado\" o null
  },
  \"input\": {
    \"voltage\": número o null,
    \"frequency\": número o null
  },
  \"output\": {
    \"voltage\": número o null,
    \"frequency\": número o null,
    \"power\": número o null
  },
  \"battery\": {
    \"voltage\": número o null,
    \"percentage\": número o null,
    \"tiempo_respaldo_min\": número o null,
    \"tiempo_descarga_min\": número o null,
    \"estado\": \"charging\" o \"discharging\" o \"standby\" o null
  },
  \"fases\": {
    \"a\": {\"voltage\": número o null, \"corriente\": número o null, \"frecuencia\": número o null},
    \"b\": {\"voltage\": número o null, \"corriente\": número o null, \"frecuencia\": número o null},
    \"c\": {\"voltage\": número o null, \"corriente\": número o null, \"frecuencia\": número o null}
  },
  \"alarmas\": [\"texto de alarma 1\", \"texto de alarma 2\"],
  \"temperatura\": número o null,
  \"modelo_ups\": \"texto del modelo\" o null,
  \"datos_adicionales\": {\"campo\": \"valor\"}
}

Si no puedes ver algún valor, usa null. Solo responde con el JSON, sin texto adicional.";

            // Procesar cada imagen
            foreach ($request->file('imagenes') as $index => $imagen) {
                // Guardar imagen temporalmente
                $imagenPath = $imagen->store('ups/bitacora/temp', 'public');
                $imagenesPaths[] = $imagenPath;
                $imagenFullPath = storage_path('app/public/' . $imagenPath);

                // Convertir imagen a base64 para OpenAI
                $imagenBase64 = base64_encode(file_get_contents($imagenFullPath));
                $mimeType = $imagen->getMimeType();

                // Llamar a OpenAI GPT-4 Vision
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $prompt,
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:{$mimeType};base64,{$imagenBase64}",
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'max_tokens' => 1000,
                ]);

                $content = $response->choices[0]->message->content;

                // Limpiar el contenido (puede venir con markdown code blocks)
                $content = trim($content);
                if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                    $content = $matches[1];
                } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $matches)) {
                    $content = $matches[1];
                }

                // Parsear JSON
                $datos = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Error al parsear respuesta de OpenAI para imagen ' . ($index + 1) . ': ' . json_last_error_msg());
                    continue; // Continuar con la siguiente imagen
                }

                // Guardar datos de esta imagen
                $imagenesData[] = [
                    'path' => $imagenPath,
                    'datos' => $datos,
                ];

                // Combinar datos (priorizar valores no nulos)
                if (isset($datos['indicadores'])) {
                    foreach (['normal', 'battery', 'bypass', 'fault'] as $ind) {
                        if (isset($datos['indicadores'][$ind]) && $datos['indicadores'][$ind] === true) {
                            $datosCombinados['indicadores'][$ind] = true;
                        }
                    }
                }

                if (isset($datos['colores_indicadores'])) {
                    foreach (['normal', 'battery', 'bypass', 'fault'] as $ind) {
                        if (isset($datos['colores_indicadores'][$ind]) && $datos['colores_indicadores'][$ind] !== null) {
                            $datosCombinados['colores_indicadores'][$ind] = $datos['colores_indicadores'][$ind];
                        }
                    }
                }

                // Combinar input/output/battery (usar el primer valor no nulo encontrado)
                foreach (['input', 'output', 'battery'] as $seccion) {
                    if (isset($datos[$seccion]) && is_array($datos[$seccion])) {
                        foreach ($datos[$seccion] as $key => $value) {
                            if ($value !== null && (!isset($datosCombinados[$seccion][$key]) || $datosCombinados[$seccion][$key] === null)) {
                                $datosCombinados[$seccion][$key] = $value;
                            }
                        }
                    }
                }

                // Combinar temperatura
                if (isset($datos['temperatura']) && $datos['temperatura'] !== null && $datosCombinados['temperatura'] === null) {
                    $datosCombinados['temperatura'] = $datos['temperatura'];
                }

                // Combinar fases
                if (isset($datos['fases']) && is_array($datos['fases'])) {
                    foreach (['a', 'b', 'c'] as $fase) {
                        if (isset($datos['fases'][$fase]) && is_array($datos['fases'][$fase])) {
                            foreach (['voltage', 'corriente', 'frecuencia'] as $param) {
                                if (isset($datos['fases'][$fase][$param]) && $datos['fases'][$fase][$param] !== null) {
                                    if (!isset($datosCombinados['fases'][$fase]) || !is_array($datosCombinados['fases'][$fase])) {
                                        $datosCombinados['fases'][$fase] = ['voltage' => null, 'corriente' => null, 'frecuencia' => null];
                                    }
                                    if (!isset($datosCombinados['fases'][$fase][$param]) || $datosCombinados['fases'][$fase][$param] === null) {
                                        $datosCombinados['fases'][$fase][$param] = $datos['fases'][$fase][$param];
                                    }
                                }
                            }
                        }
                    }
                }

                // Combinar alarmas
                if (isset($datos['alarmas']) && is_array($datos['alarmas'])) {
                    $datosCombinados['alarmas'] = array_merge($datosCombinados['alarmas'], $datos['alarmas']);
                }

                // Modelo UPS (usar el primero encontrado)
                if (isset($datos['modelo_ups']) && $datos['modelo_ups'] !== null && $datosCombinados['modelo_ups'] === null) {
                    $datosCombinados['modelo_ups'] = $datos['modelo_ups'];
                }

                // Datos adicionales
                if (isset($datos['datos_adicionales']) && is_array($datos['datos_adicionales'])) {
                    $datosCombinados['datos_adicionales'] = array_merge($datosCombinados['datos_adicionales'], $datos['datos_adicionales']);
                }
            }

            // Preparar datos para vista previa
            $previewData = [
                'imagenes' => $imagenesPaths,
                'indicador_normal' => $datosCombinados['indicadores']['normal'] ?? false,
                'indicador_battery' => $datosCombinados['indicadores']['battery'] ?? false,
                'indicador_bypass' => $datosCombinados['indicadores']['bypass'] ?? false,
                'indicador_fault' => $datosCombinados['indicadores']['fault'] ?? false,
                'colores_indicadores' => $datosCombinados['colores_indicadores'] ?? [],
                'input_voltage' => ($datosCombinados['input'] ?? [])['voltage'] ?? null,
                'input_frequency' => ($datosCombinados['input'] ?? [])['frequency'] ?? null,
                'output_voltage' => ($datosCombinados['output'] ?? [])['voltage'] ?? null,
                'output_frequency' => ($datosCombinados['output'] ?? [])['frequency'] ?? null,
                'output_power' => ($datosCombinados['output'] ?? [])['power'] ?? null,
                'battery_voltage' => ($datosCombinados['battery'] ?? [])['voltage'] ?? null,
                'battery_percentage' => ($datosCombinados['battery'] ?? [])['percentage'] ?? null,
                'battery_tiempo_respaldo' => isset($datosCombinados['battery']['tiempo_respaldo_min']) ? $datosCombinados['battery']['tiempo_respaldo_min'] : null,
                'battery_tiempo_descarga' => isset($datosCombinados['battery']['tiempo_descarga_min']) ? $datosCombinados['battery']['tiempo_descarga_min'] : null,
                'battery_estado' => isset($datosCombinados['battery']['estado']) ? $datosCombinados['battery']['estado'] : null,
                'temperatura' => $datosCombinados['temperatura'] ?? null,
                'fases' => $datosCombinados['fases'] ?? [],
                'alarmas' => array_unique($datosCombinados['alarmas'] ?? []),
                'modelo_ups' => $datosCombinados['modelo_ups'] ?? null,
                'datos_extraidos' => $datosCombinados,
                'imagenes_analizadas' => $imagenesData,
            ];

            return response()->json([
                'success' => true,
                'preview' => $previewData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al analizar imagen UPS: ' . $e->getMessage(), [
                'ups_id' => $ups->id,
                'exception' => $e,
            ]);

            // Limpiar imágenes temporales si existen
            foreach ($imagenesPaths as $imgPath) {
                if (Storage::disk('public')->exists($imgPath)) {
                    Storage::disk('public')->delete($imgPath);
                }
            }

            // Mensaje de error más amigable
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'billing')) {
                $errorMessage = 'La cuenta de OpenAI no tiene créditos disponibles. Por favor, verifica tu plan y facturación en https://platform.openai.com/account/billing. Mientras tanto, puedes ingresar los datos manualmente en el formulario.';
            } elseif (str_contains($errorMessage, 'rate_limit')) {
                $errorMessage = 'Se alcanzó el límite de solicitudes. Por favor, espera unos momentos e intenta nuevamente.';
            } elseif (str_contains($errorMessage, 'invalid_api_key')) {
                $errorMessage = 'La API key de OpenAI no es válida. Por favor, verifica la configuración.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'allow_manual' => true, // Permitir entrada manual como alternativa
            ], 500);
        }
    }

    public function store(Request $request, Ups $ups)
    {
        $this->authorize('view', $ups);

        $request->validate([
            'imagenes' => 'nullable|array|max:5', // Rutas de imágenes temporales (si viene del análisis)
            'imagenes.*' => 'nullable|string',
            'imagenes_files' => 'nullable|array|max:5', // Archivos de imagen (si viene de entrada manual)
            'imagenes_files.*' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'indicador_normal' => 'boolean',
            'indicador_battery' => 'boolean',
            'indicador_bypass' => 'boolean',
            'indicador_fault' => 'boolean',
            'input_voltage' => 'nullable|numeric|min:0|max:1000',
            'input_frequency' => 'nullable|numeric|min:0|max:100',
            'output_voltage' => 'nullable|numeric|min:0|max:1000',
            'output_frequency' => 'nullable|numeric|min:0|max:100',
            'output_power' => 'nullable|numeric|min:0',
            'battery_voltage' => 'nullable|numeric|min:0|max:500',
            'battery_percentage' => 'nullable|integer|min:0|max:100',
            'battery_tiempo_respaldo' => 'nullable|integer|min:0',
            'battery_tiempo_descarga' => 'nullable|integer|min:0',
            'battery_estado' => 'nullable|string|max:50',
            'temperatura' => 'nullable|numeric|min:-50|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'datos_extraidos' => 'nullable|string', // JSON string
        ]);

        try {
            $imagenesFinales = [];

            // Si vienen archivos nuevos (entrada manual)
            if ($request->hasFile('imagenes_files')) {
                foreach ($request->file('imagenes_files') as $index => $imagen) {
                    if ($imagen) {
                        $imagenFinal = $imagen->store('ups/bitacora', 'public');
                        $imagenesFinales[] = [
                            'ruta' => $imagenFinal,
                            'orden' => $index,
                        ];
                    }
                }
            }
            // Si vienen rutas de imágenes temporales (análisis exitoso)
            elseif ($request->has('imagenes') && is_array($request->input('imagenes'))) {
                foreach ($request->input('imagenes') as $index => $imagenTemp) {
                    if ($imagenTemp) {
                        $imagenFinal = str_replace('ups/bitacora/temp', 'ups/bitacora', $imagenTemp);

                        if (Storage::disk('public')->exists($imagenTemp)) {
                            Storage::disk('public')->move($imagenTemp, $imagenFinal);
                            $imagenesFinales[] = [
                                'ruta' => $imagenFinal,
                                'orden' => $index,
                            ];
                        } else {
                            Log::warning('Imagen temporal no existe: ' . $imagenTemp);
                        }
                    }
                }
            }

            if (empty($imagenesFinales)) {
                throw new \Exception('Debe proporcionar al menos una imagen');
            }

            // Parsear datos_extraidos si viene como JSON string
            $datosExtraidos = null;
            if ($request->has('datos_extraidos') && $request->input('datos_extraidos')) {
                $datosExtraidos = is_string($request->input('datos_extraidos'))
                    ? json_decode($request->input('datos_extraidos'), true)
                    : $request->input('datos_extraidos');
            }

            // Helper: conservar 0 como valor válido (?: convierte 0 en null)
            $num = fn ($key) => $request->has($key) && $request->input($key) !== '' && $request->input($key) !== null
                ? $request->input($key)
                : null;
            $str = fn ($key) => $request->filled($key) ? $request->input($key) : null;

            // Crear registro de bitácora
            $bitacora = UpsBitacora::create([
                'ups_id' => $ups->id,
                'indicador_normal' => (bool) $request->input('indicador_normal', false),
                'indicador_battery' => (bool) $request->input('indicador_battery', false),
                'indicador_bypass' => (bool) $request->input('indicador_bypass', false),
                'indicador_fault' => (bool) $request->input('indicador_fault', false),
                'input_voltage' => $num('input_voltage'),
                'input_frequency' => $num('input_frequency'),
                'output_voltage' => $num('output_voltage'),
                'output_frequency' => $num('output_frequency'),
                'output_power' => $num('output_power'),
                'battery_voltage' => $num('battery_voltage'),
                'battery_percentage' => $num('battery_percentage'),
                'battery_tiempo_respaldo' => $num('battery_tiempo_respaldo'),
                'battery_tiempo_descarga' => $num('battery_tiempo_descarga'),
                'battery_estado' => $str('battery_estado'),
                'temperatura' => $num('temperatura'),
                'datos_extraidos' => $datosExtraidos,
                'observaciones' => $str('observaciones'),
                'created_by' => auth()->id(),
            ]);

            // Guardar múltiples imágenes
            foreach ($imagenesFinales as $img) {
                UpsBitacoraImagen::create([
                    'ups_vitacora_id' => $bitacora->id,
                    'ruta_imagen' => $img['ruta'],
                    'orden' => $img['orden'],
                    'descripcion' => null,
                ]);
            }

            return redirect()
                ->route('ups.bitacora.index', ['ups' => $ups->id])
                ->with('message', 'Registro de bitácora guardado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al guardar bitácora UPS: ' . $e->getMessage(), [
                'ups_id' => $ups->id,
                'exception' => $e,
            ]);

            return back()->withErrors([
                'error' => 'Error al guardar el registro: ' . $e->getMessage(),
            ]);
        }
    }

    public function destroy(Ups $ups, UpsBitacora $bitacora)
    {
        $this->authorize('view', $ups);

        // Eliminar todas las imágenes asociadas
        foreach ($bitacora->imagenes as $imagen) {
            if (Storage::disk('public')->exists($imagen->ruta_imagen)) {
                Storage::disk('public')->delete($imagen->ruta_imagen);
            }
        }

        $bitacora->delete();

        return redirect()
            ->route('ups.bitacora.index', ['ups' => $ups->id])
            ->with('message', 'Registro eliminado exitosamente.');
    }

    public function export(Request $request, Ups $ups)
    {
        $this->authorize('view', $ups);

        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        if (!$fechaDesde || !$fechaHasta) {
            abort(400, 'Debe especificar fecha_desde y fecha_hasta');
        }

        // Obtener bitácoras en el rango de fechas
        $bitacoras = $ups->bitacora()
            ->with(['creadoPor', 'imagenes'])
            ->whereDate('created_at', '>=', $fechaDesde)
            ->whereDate('created_at', '<=', $fechaHasta)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($bitacoras->isEmpty()) {
            abort(404, 'No hay registros de bitácora en el rango de fechas especificado');
        }

        // Generar nombre del ZIP: nombre_ups + fecha
        $nombreUps = preg_replace('/[^A-Za-z0-9_-]+/', '_', $ups->nombre ?: $ups->codigo ?: 'ups_' . $ups->id);
        $fechaInicio = str_replace('-', '', $fechaDesde);
        $fechaFin = str_replace('-', '', $fechaHasta);
        $zipName = "bitacora_{$nombreUps}_{$fechaInicio}_{$fechaFin}.zip";

        // Verificar disponibilidad de ZipArchive
        $useZipArchive = extension_loaded('zip') && class_exists(ZipArchive::class);

        if ($useZipArchive) {
            return $this->exportWithZipArchive($bitacoras, $ups, $zipName);
        } else {
            return $this->exportWithZipStream($bitacoras, $ups, $zipName);
        }
    }

    private function exportWithZipArchive($bitacoras, Ups $ups, string $zipName)
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

        foreach ($bitacoras as $index => $bitacora) {
            $bitacora->load(['creadoPor', 'imagenes']);
            $fechaRegistro = $bitacora->created_at->format('Y-m-d_H-i-s');
            $folderName = sprintf('bitacora_%02d_%s', $index + 1, $fechaRegistro);

            // Generar PDF con los datos
            $pdf = Pdf::loadView('ups.bitacora.pdf', [
                'bitacora' => $bitacora,
                'ups' => $ups,
            ]);
            $pdfContent = $pdf->output();
            $zip->addFromString("{$folderName}/datos.pdf", $pdfContent);

            // Agregar imágenes
            foreach ($bitacora->imagenes->sortBy('orden') as $imgIndex => $imagen) {
                $rel = $imagen->ruta_imagen;
                if (!$rel || !Storage::disk('public')->exists($rel)) {
                    continue;
                }
                $abs = storage_path('app/public/' . $rel);
                if (!is_file($abs)) {
                    continue;
                }
                $ext = pathinfo($rel, PATHINFO_EXTENSION) ?: 'jpg';
                $imgName = sprintf('imagen_%02d.%s', $imgIndex + 1, $ext);
                $zip->addFile($abs, "{$folderName}/fotos/{$imgName}");
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    private function exportWithZipStream($bitacoras, Ups $ups, string $zipName)
    {
        return response()->streamDownload(function () use ($bitacoras, $ups, $zipName) {
            $outputStream = fopen('php://output', 'w');
            if (!$outputStream) {
                abort(500, 'No se pudo abrir el stream de salida.');
            }

            $zip = new ZipStream(
                outputStream: $outputStream,
                sendHttpHeaders: false,
                outputName: $zipName,
            );

            foreach ($bitacoras as $index => $bitacora) {
                $bitacora->load(['creadoPor', 'imagenes']);
                $fechaRegistro = $bitacora->created_at->format('Y-m-d_H-i-s');
                $folderName = sprintf('bitacora_%02d_%s', $index + 1, $fechaRegistro);

                // Generar PDF con los datos
                $pdf = Pdf::loadView('ups.bitacora.pdf', [
                    'bitacora' => $bitacora,
                    'ups' => $ups,
                ]);
                $pdfContent = $pdf->output();
                $zip->addFile(
                    fileName: "{$folderName}/datos.pdf",
                    data: $pdfContent
                );

                // Agregar imágenes
                foreach ($bitacora->imagenes->sortBy('orden') as $imgIndex => $imagen) {
                    $rel = $imagen->ruta_imagen;
                    if (!$rel || !Storage::disk('public')->exists($rel)) {
                        continue;
                    }
                    $abs = storage_path('app/public/' . $rel);
                    if (!is_file($abs)) {
                        continue;
                    }
                    $ext = pathinfo($rel, PATHINFO_EXTENSION) ?: 'jpg';
                    $imgName = sprintf('imagen_%02d.%s', $imgIndex + 1, $ext);
                    $zip->addFileFromPath(
                        fileName: "{$folderName}/fotos/{$imgName}",
                        path: $abs
                    );
                }
            }

            $zip->finish();
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    private function generateHtmlDocument(UpsBitacora $bitacora, Ups $ups): string
    {
        $creadoPor = $bitacora->creadoPor;
        $usuarioNombre = 'Desconocido';
        if ($creadoPor) {
            if ($creadoPor->nombre && $creadoPor->apellido) {
                $usuarioNombre = "{$creadoPor->nombre} {$creadoPor->apellido}";
            } elseif ($creadoPor->name) {
                $usuarioNombre = $creadoPor->name;
            } elseif ($creadoPor->email) {
                $usuarioNombre = $creadoPor->email;
            }
        }

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora UPS - ' . htmlspecialchars($ups->codigo ?? $ups->nombre) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; color: #333; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 25px; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0; }
        .info-box { background: #f8f9fa; border-left: 4px solid #3498db; padding: 15px; border-radius: 5px; }
        .info-box h3 { margin: 0 0 10px 0; color: #2c3e50; font-size: 16px; }
        .info-item { margin: 8px 0; }
        .info-label { font-weight: bold; color: #555; }
        .indicador { display: inline-block; padding: 5px 10px; border-radius: 5px; font-weight: bold; margin: 5px 5px 5px 0; }
        .indicador.on { background: #2ecc71; color: white; }
        .indicador.off { background: #95a5a6; color: white; }
        .observaciones { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .metadata { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Bitácora UPS: ' . htmlspecialchars($ups->codigo ?? $ups->nombre) . '</h1>

    <div class="metadata">
        <p><strong>Fecha de registro:</strong> ' . $bitacora->created_at->format('d/m/Y H:i:s') . '</p>
        <p><strong>Registrado por:</strong> ' . htmlspecialchars($usuarioNombre) . '</p>
        <p><strong>UPS:</strong> ' . htmlspecialchars($ups->codigo ?? $ups->nombre) . ' - ' . htmlspecialchars($ups->nombre ?? '') . '</p>
    </div>

    <h2>Indicadores</h2>
    <div>
        <span class="indicador ' . ($bitacora->indicador_normal ? 'on' : 'off') . '">NORMAL: ' . ($bitacora->indicador_normal ? 'ON' : 'OFF') . '</span>
        <span class="indicador ' . ($bitacora->indicador_battery ? 'on' : 'off') . '">BATTERY: ' . ($bitacora->indicador_battery ? 'ON' : 'OFF') . '</span>
        <span class="indicador ' . ($bitacora->indicador_bypass ? 'on' : 'off') . '">BYPASS: ' . ($bitacora->indicador_bypass ? 'ON' : 'OFF') . '</span>
        <span class="indicador ' . ($bitacora->indicador_fault ? 'on' : 'off') . '">FAULT: ' . ($bitacora->indicador_fault ? 'ON' : 'OFF') . '</span>
    </div>

    <h2>Datos Técnicos</h2>
    <div class="info-grid">
        <div class="info-box">
            <h3>Input</h3>
            <div class="info-item"><span class="info-label">Voltaje:</span> ' . ($bitacora->input_voltage ? number_format($bitacora->input_voltage, 2) . ' V' : '-') . '</div>
            <div class="info-item"><span class="info-label">Frecuencia:</span> ' . ($bitacora->input_frequency ? number_format($bitacora->input_frequency, 2) . ' Hz' : '-') . '</div>
        </div>
        <div class="info-box">
            <h3>Output</h3>
            <div class="info-item"><span class="info-label">Voltaje:</span> ' . ($bitacora->output_voltage ? number_format($bitacora->output_voltage, 2) . ' V' : '-') . '</div>
            <div class="info-item"><span class="info-label">Frecuencia:</span> ' . ($bitacora->output_frequency ? number_format($bitacora->output_frequency, 2) . ' Hz' : '-') . '</div>
            <div class="info-item"><span class="info-label">Potencia:</span> ' . ($bitacora->output_power ? number_format($bitacora->output_power, 2) . ' W' : '-') . '</div>
        </div>
        <div class="info-box">
            <h3>Battery</h3>
            <div class="info-item"><span class="info-label">Voltaje:</span> ' . ($bitacora->battery_voltage ? number_format($bitacora->battery_voltage, 2) . ' V' : '-') . '</div>
            <div class="info-item"><span class="info-label">Porcentaje:</span> ' . ($bitacora->battery_percentage !== null ? $bitacora->battery_percentage . '%' : '-') . '</div>';

        if ($bitacora->battery_tiempo_respaldo !== null) {
            $html .= '<div class="info-item"><span class="info-label">Tiempo Respaldo:</span> ' . $bitacora->battery_tiempo_respaldo . ' min</div>';
        }
        if ($bitacora->battery_tiempo_descarga !== null) {
            $html .= '<div class="info-item"><span class="info-label">Tiempo Descarga:</span> ' . $bitacora->battery_tiempo_descarga . ' min</div>';
        }
        if ($bitacora->battery_estado) {
            $html .= '<div class="info-item"><span class="info-label">Estado:</span> ' . htmlspecialchars(ucfirst($bitacora->battery_estado)) . '</div>';
        }

        $html .= '</div>';

        if ($bitacora->temperatura !== null) {
            $html .= '<div class="info-box">
                <h3>Temperatura</h3>
                <div class="info-item"><span class="info-label">Temperatura:</span> ' . number_format($bitacora->temperatura, 2) . ' °C</div>
            </div>';
        }

        $html .= '</div>';

        if ($bitacora->observaciones) {
            $html .= '<h2>Observaciones</h2>
            <div class="observaciones">
                ' . nl2br(htmlspecialchars($bitacora->observaciones)) . '
            </div>';
        }

        if ($bitacora->imagenes && $bitacora->imagenes->count() > 0) {
            $html .= '<h2>Imágenes (' . $bitacora->imagenes->count() . ')</h2>
            <p>Las imágenes se encuentran en la carpeta <strong>fotos/</strong> de este directorio.</p>';
        }

        $html .= '</body>
</html>';

        return $html;
    }
}
