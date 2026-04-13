<?php

namespace App\Http\Controllers;

use App\Models\Ups;
use App\Models\UpsBitacora;
use App\Support\UpsBitacoraVisionEnricher;
use App\Support\UpsBitacoraVisionPrompt;
use App\Support\UpsUmbralesEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use OpenAI\Laravel\Facades\OpenAI;

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

        $bitacora->getCollection()->transform(static function (UpsBitacora $b) use ($ups) {
            $row = $b->toArray();
            $row['umbrales_alerta'] = UpsUmbralesEvaluator::alertas($ups, $b);

            return $row;
        });

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

        if (!config('openai.ups_bitacora_ai_enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'El análisis asistido por IA está desactivado (UPS_BITACORA_AI_ENABLED). Puedes registrar la lectura manualmente.',
                'allow_manual' => true,
            ], 422);
        }

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

            $imageDetail = strtolower((string) config('openai.ups_bitacora_image_detail', 'high'));
            if (! in_array($imageDetail, ['auto', 'low', 'high'], true)) {
                $imageDetail = 'high';
            }

            $visionInstructions = UpsBitacoraVisionPrompt::instructions();

            // Procesar cada imagen
            foreach ($request->file('imagenes') as $index => $imagen) {
                // Guardar imagen temporalmente
                $imagenPath = $imagen->store('ups/bitacora/temp', 'public');
                $imagenesPaths[] = $imagenPath;
                $imagenFullPath = storage_path('app/public/' . $imagenPath);

                // Convertir imagen a base64 para OpenAI
                $imagenBase64 = base64_encode(file_get_contents($imagenFullPath));
                $mimeType = $imagen->getMimeType();

                $createParams = [
                    'model' => config('openai.ups_bitacora_model', 'gpt-4o'),
                    'max_tokens' => (int) config('openai.ups_bitacora_max_output_tokens', 2000),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un extractor estricto de lecturas de equipos industriales. Cumples las instrucciones del usuario y respondes únicamente con el JSON solicitado, sin bloques markdown.',
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $visionInstructions,
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:{$mimeType};base64,{$imagenBase64}",
                                        'detail' => $imageDetail,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];

                if (config('openai.ups_bitacora_json_object_mode', true)) {
                    $createParams['response_format'] = ['type' => 'json_object'];
                }

                $response = OpenAI::chat()->create($createParams);

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

            // Rellenar input/output/power desde fases y datos_adicionales si el modelo dejó todo en tablas A/B/C
            UpsBitacoraVisionEnricher::enrich($datosCombinados);

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
            'imagenes' => 'nullable|array|max:5',
            'imagenes.*' => 'nullable|string',
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
            'datos_extraidos' => 'nullable|string',
        ]);

        $datosExtraidos = null;
        if ($request->has('datos_extraidos') && $request->input('datos_extraidos')) {
            $datosExtraidos = is_string($request->input('datos_extraidos'))
                ? json_decode($request->input('datos_extraidos'), true)
                : $request->input('datos_extraidos');
        }

        try {
            $this->validarLecturasContraDatosIa($request, $datosExtraidos);

            $num = fn ($key) => $request->has($key) && $request->input($key) !== '' && $request->input($key) !== null
                ? $request->input($key)
                : null;
            $str = fn ($key) => $request->filled($key) ? $request->input($key) : null;

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

            $this->eliminarImagenesTemporalesBitacora($request);

            return redirect()
                ->route('ups.bitacora.index', ['ups' => $ups->id])
                ->with('message', 'Registro de bitácora guardado exitosamente.');
        } catch (ValidationException $e) {
            throw $e;
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

    private function validarLecturasContraDatosIa(Request $request, ?array $datosExtraidos): void
    {
        if (!$datosExtraidos || !is_array($datosExtraidos) || !config('openai.ups_bitacora_ai_enabled', true)) {
            return;
        }
        $tol = (float) config('openai.ups_bitacora_compare_tolerance_pct', 8);
        if ($tol <= 0) {
            return;
        }

        $checks = [
            ['request' => 'input_voltage', 'dot' => 'input.voltage'],
            ['request' => 'input_frequency', 'dot' => 'input.frequency'],
            ['request' => 'output_voltage', 'dot' => 'output.voltage'],
            ['request' => 'output_frequency', 'dot' => 'output.frequency'],
            ['request' => 'output_power', 'dot' => 'output.power'],
            ['request' => 'battery_voltage', 'dot' => 'battery.voltage'],
            ['request' => 'battery_percentage', 'dot' => 'battery.percentage'],
            ['request' => 'temperatura', 'dot' => 'temperatura'],
        ];

        $errors = [];
        foreach ($checks as $c) {
            $expected = data_get($datosExtraidos, $c['dot']);
            if ($expected === null || $expected === '') {
                continue;
            }
            $raw = $request->input($c['request']);
            if ($raw === null || $raw === '') {
                continue;
            }
            $actual = (float) $raw;
            $exp = (float) $expected;
            if (!$this->withinTolerance($actual, $exp, $tol)) {
                $errors[$c['request']] = [
                    'El valor no coincide con lo inferido en la imagen dentro de la tolerancia configurada. Ajusta el campo o vuelve a analizar la foto.',
                ];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function withinTolerance(float $actual, float $expected, float $tolerancePct): bool
    {
        if ($expected == 0.0) {
            return abs($actual) <= max(0.01, abs($tolerancePct / 100) * 1);
        }
        $rel = abs($actual - $expected) / abs($expected);

        return ($rel * 100) <= $tolerancePct;
    }

    private function eliminarImagenesTemporalesBitacora(Request $request): void
    {
        if (!$request->has('imagenes') || !is_array($request->input('imagenes'))) {
            return;
        }
        foreach ($request->input('imagenes') as $path) {
            if (!$path || !is_string($path)) {
                continue;
            }
            if (str_starts_with($path, 'ups/bitacora/temp/') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
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
}
