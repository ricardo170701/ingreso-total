<?php

namespace Database\Seeders;

use App\Models\Piso;
use App\Models\Ups;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportarUpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Importa UPS desde el archivo Excel "UPS.xlsx"
     * 
     * Columnas esperadas:
     * - Comp
     * - Fecha de adquisición
     * - Elemt
     * - R.I.
     * - Nombre
     * - Piso
     * - Estado
     * - Marca
     * - Modelo
     * - Serial
     * - Ubicación
     * - potencia(KVA)
     * - Potencia(KW)
     * - Cantidad baterías
     * - Voltaje de baterías
     * - Ficha técnica
     */
    public function run(): void
    {
        $archivoExcel = base_path('UPS.xlsx');

        if (!file_exists($archivoExcel)) {
            $this->command->error("❌ No se encontró el archivo Excel: {$archivoExcel}");
            $this->command->info("Asegúrate de que el archivo 'UPS.xlsx' esté en la raíz del proyecto.");
            return;
        }

        $this->command->info("📋 Iniciando importación de UPS desde Excel...");
        $this->command->info("📂 Archivo: {$archivoExcel}");
        $this->command->newLine();

        // Leer el archivo Excel
        try {
            $datos = Excel::toArray([], $archivoExcel);

            if (empty($datos) || empty($datos[0])) {
                $this->command->error("❌ El archivo Excel está vacío o no tiene datos.");
                return;
            }

            // La primera hoja contiene los datos
            $filas = $datos[0];

            // La primera fila contiene los encabezados
            $encabezadosOriginales = $filas[0];
            $encabezados = array_map('trim', array_map('strtolower', $encabezadosOriginales));
            $this->command->info("📊 Encabezados encontrados: " . implode(', ', $encabezados));
            $this->command->newLine();

            // Mapeo de encabezados (flexible, puede variar el orden)
            $indices = [];
            foreach ($encabezados as $index => $encabezado) {
                $encabezadoLimpio = Str::slug($encabezado, '_');
                $indices[$encabezadoLimpio] = $index;
                // También guardar el original en minúsculas para búsqueda directa
                $indices[strtolower(trim($encabezado))] = $index;
            }

            // Obtener índices de columnas con búsqueda más flexible
            $getIndex = function ($variantes) use ($indices, $encabezados, $encabezadosOriginales) {
                foreach ($variantes as $variante) {
                    // Normalizar la variante para búsqueda
                    $varianteLower = strtolower(trim($variante));
                    
                    // Primero buscar coincidencia exacta (case-insensitive)
                    foreach ($encabezadosOriginales as $idx => $encabezadoOriginal) {
                        if (strtolower(trim($encabezadoOriginal)) === $varianteLower) {
                            return $idx;
                        }
                    }
                    
                    // Luego buscar en los índices ya procesados
                    if (isset($indices[$varianteLower])) {
                        return $indices[$varianteLower];
                    }
                    
                    // Finalmente buscar coincidencias parciales en los encabezados (case-insensitive)
                    foreach ($encabezados as $idx => $encabezado) {
                        // Eliminar espacios y convertir a minúsculas para comparación flexible
                        $encabezadoNormalizado = strtolower(str_replace([' ', '_', '-'], '', $encabezado));
                        $varianteNormalizada = strtolower(str_replace([' ', '_', '-'], '', $variante));
                        
                        // Buscar coincidencia parcial
                        if (stripos($encabezado, $varianteLower) !== false || 
                            stripos($varianteLower, $encabezado) !== false ||
                            stripos($encabezadoNormalizado, $varianteNormalizada) !== false) {
                            return $idx;
                        }
                    }
                }
                return null;
            };

            $idxComp = $getIndex(['comp', 'compañía', 'compartimiento']);
            $idxFechaAdquisicion = $getIndex(['fecha_de_adquisicion', 'fecha-adquisicion', 'fecha de adquisición', 'fecha_de_adquisición', 'fecha adquisicion']);
            $idxElemt = $getIndex(['elemt', 'elemento']);
            $idxRi = $getIndex(['r_i', 'ri', 'r.i.', 'registro_interno', 'r i']);
            $idxNombre = $getIndex(['nombre']);
            $idxPiso = $getIndex(['piso']);
            $idxEstado = $getIndex(['estado']);
            $idxMarca = $getIndex(['marca']);
            $idxModelo = $getIndex(['modelo']);
            $idxSerial = $getIndex(['serial', 'serie']);
            $idxUbicacion = $getIndex(['ubicación', 'ubicacion', 'ubicaci']);
            // Buscar nombres exactos con paréntesis
            $idxPotenciaKva = $getIndex(['potencia(kva)', 'potencia(kva', 'potencia_kva', 'potencia-kva', 'potencia kva', 'potencia', 'kva']);
            $idxPotenciaKw = $getIndex(['potencia(kw)', 'potencia(kw', 'potencia_kw', 'potencia-kw', 'potencia kw', 'kw']);
            $idxCantidadBaterias = $getIndex(['cantidad baterías', 'cantidad_baterías', 'cantidad_baterias', 'cantidad-baterías', 'cantidad baterias', 'cantidad_bater', 'cantidad bater']);
            $idxVoltajeBaterias = $getIndex(['voltaje de baterías', 'voltaje_de_baterías', 'voltaje_baterias', 'voltaje-baterías', 'voltaje baterias', 'voltaje baterías', 'voltaje_de_bater', 'voltaje bater']);
            $idxFichaTecnica = $getIndex(['ficha_técnica', 'ficha_tecnica', 'ficha-técnica', 'ficha tecnica', 'ficha_técnica', 'ficha']);
            
            // Debug: Mostrar qué columnas se encontraron
            $this->command->info("🔍 Columnas detectadas:");
            if ($idxPotenciaKva !== null && isset($encabezadosOriginales[$idxPotenciaKva])) {
                $this->command->comment("  ✓ Potencia KVA: Columna " . $idxPotenciaKva . " - '{$encabezadosOriginales[$idxPotenciaKva]}'");
            } else {
                $this->command->warn("  ❌ Potencia KVA: NO ENCONTRADA");
            }
            if ($idxPotenciaKw !== null && isset($encabezadosOriginales[$idxPotenciaKw])) {
                $this->command->comment("  ✓ Potencia KW: Columna " . $idxPotenciaKw . " - '{$encabezadosOriginales[$idxPotenciaKw]}'");
            } else {
                $this->command->warn("  ❌ Potencia KW: NO ENCONTRADA");
            }
            if ($idxVoltajeBaterias !== null && isset($encabezadosOriginales[$idxVoltajeBaterias])) {
                $this->command->comment("  ✓ Voltaje Baterías: Columna " . $idxVoltajeBaterias . " - '{$encabezadosOriginales[$idxVoltajeBaterias]}'");
            } else {
                $this->command->warn("  ❌ Voltaje Baterías: NO ENCONTRADA");
            }
            $this->command->newLine();

            // Contadores
            $upsCreadas = 0;
            $upsActualizadas = 0;
            $pisosCreados = 0;
            $errores = [];

            DB::beginTransaction();

            try {
                // Procesar cada fila (omitir la primera que son los encabezados)
                for ($i = 1; $i < count($filas); $i++) {
                    $fila = $filas[$i];

                    // Obtener valores de la fila
                    $comp = ($idxComp !== null && isset($fila[$idxComp])) ? trim($fila[$idxComp]) : null;
                    $fechaAdquisicion = ($idxFechaAdquisicion !== null && isset($fila[$idxFechaAdquisicion])) ? trim($fila[$idxFechaAdquisicion]) : null;
                    $elemt = ($idxElemt !== null && isset($fila[$idxElemt])) ? trim($fila[$idxElemt]) : null;
                    $ri = ($idxRi !== null && isset($fila[$idxRi])) ? trim($fila[$idxRi]) : null;
                    $nombre = ($idxNombre !== null && isset($fila[$idxNombre])) ? trim($fila[$idxNombre]) : '';
                    $pisoNombre = ($idxPiso !== null && isset($fila[$idxPiso])) ? trim($fila[$idxPiso]) : '';
                    $estado = ($idxEstado !== null && isset($fila[$idxEstado])) ? trim($fila[$idxEstado]) : null;
                    $marca = ($idxMarca !== null && isset($fila[$idxMarca])) ? trim($fila[$idxMarca]) : null;
                    $modelo = ($idxModelo !== null && isset($fila[$idxModelo])) ? trim($fila[$idxModelo]) : null;
                    $serial = ($idxSerial !== null && isset($fila[$idxSerial])) ? trim($fila[$idxSerial]) : null;
                    $ubicacion = ($idxUbicacion !== null && isset($fila[$idxUbicacion])) ? trim($fila[$idxUbicacion]) : null;
                    $potenciaKva = ($idxPotenciaKva !== null && isset($fila[$idxPotenciaKva])) ? trim($fila[$idxPotenciaKva]) : null;
                    $potenciaKw = ($idxPotenciaKw !== null && isset($fila[$idxPotenciaKw])) ? trim($fila[$idxPotenciaKw]) : null;
                    $cantidadBaterias = ($idxCantidadBaterias !== null && isset($fila[$idxCantidadBaterias])) ? trim($fila[$idxCantidadBaterias]) : null;
                    $voltajeBaterias = ($idxVoltajeBaterias !== null && isset($fila[$idxVoltajeBaterias])) ? trim($fila[$idxVoltajeBaterias]) : null;
                    $fichaTecnica = ($idxFichaTecnica !== null && isset($fila[$idxFichaTecnica])) ? trim($fila[$idxFichaTecnica]) : null;

                    // Validar campos requeridos
                    if (empty($nombre)) {
                        $errores[] = "Fila " . ($i + 1) . ": Nombre vacío";
                        continue;
                    }

                    // Generar código único si no existe (usar serial o nombre)
                    $codigo = $serial ?: Str::slug($nombre, '-');
                    if (empty($codigo)) {
                        $codigo = 'UPS-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                    }

                    // Normalizar fecha de adquisición
                    $fechaAdquisicionNormalizada = null;
                    if ($fechaAdquisicion && $fechaAdquisicion !== '') {
                        try {
                            if (is_numeric($fechaAdquisicion)) {
                                // Fecha de Excel (número de días desde 1900)
                                $fechaAdquisicionNormalizada = Carbon::instance(
                                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaAdquisicion)
                                )->format('Y-m-d');
                            } else {
                                $fechaAdquisicionNormalizada = Carbon::parse($fechaAdquisicion)->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            // Si falla, simplemente no asignar fecha
                            $this->command->warn("  ⚠ Fila " . ($i + 1) . ": Fecha de adquisición inválida: {$fechaAdquisicion}");
                        }
                    }

                    // Buscar o crear el piso
                    $pisoId = null;
                    if (!empty($pisoNombre)) {
                        // Normalizar nombre del piso (limpiar espacios y mayúsculas)
                        $pisoNombreLimpio = trim($pisoNombre);
                        
                        $piso = Piso::query()
                            ->where('nombre', $pisoNombreLimpio)
                            ->orWhere('nombre', 'like', "%{$pisoNombreLimpio}%")
                            ->first();

                        if (!$piso) {
                            // Si no existe, crear el piso
                            $orden = (int) (Piso::query()->max('orden') ?? 0) + 1;
                            $piso = Piso::query()->create([
                                'nombre' => $pisoNombreLimpio,
                                'orden' => $orden,
                                'activo' => true,
                            ]);
                            $pisosCreados++;
                            $this->command->comment("  → Piso creado: {$pisoNombreLimpio}");
                        }

                        $pisoId = $piso->id;
                    }

                    // Normalizar valores numéricos
                    $potenciaKvaNormalizada = null;
                    if ($potenciaKva !== null && $potenciaKva !== '') {
                        $potenciaKvaNormalizada = (float) str_replace(',', '.', $potenciaKva);
                    }

                    $potenciaKwNormalizada = null;
                    if ($potenciaKw !== null && $potenciaKw !== '') {
                        $potenciaKwNormalizada = (float) str_replace(',', '.', $potenciaKw);
                    }

                    $cantidadBateriasNormalizada = null;
                    if ($cantidadBaterias !== null && $cantidadBaterias !== '') {
                        $cantidadBateriasNormalizada = (int) $cantidadBaterias;
                    }

                    $voltajeBateriasNormalizada = null;
                    if ($voltajeBaterias !== null && $voltajeBaterias !== '') {
                        $voltajeBateriasNormalizada = (float) str_replace(',', '.', $voltajeBaterias);
                    }

                    // Crear o actualizar la UPS
                    try {
                        $ups = Ups::query()->updateOrCreate(
                            ['codigo' => $codigo],
                            [
                                'codigo' => $codigo,
                                'comp' => $comp,
                                'fecha_adquisicion' => $fechaAdquisicionNormalizada,
                                'elemt' => $elemt,
                                'ri' => $ri,
                                'nombre' => $nombre,
                                'piso_id' => $pisoId,
                                'estado' => $estado,
                                'marca' => $marca,
                                'modelo' => $modelo,
                                'serial' => $serial,
                                'ubicacion' => $ubicacion,
                                'potencia_kva' => $potenciaKvaNormalizada,
                                'potencia_kw' => $potenciaKwNormalizada,
                                'cantidad_baterias' => $cantidadBateriasNormalizada,
                                'voltaje_baterias' => $voltajeBateriasNormalizada,
                                'activo' => true, // Por defecto activo
                            ]
                        );

                        if ($ups->wasRecentlyCreated) {
                            $upsCreadas++;
                            $this->command->info("✓ UPS creada: {$nombre} ({$codigo})");
                        } else {
                            $upsActualizadas++;
                            $this->command->comment("↻ UPS actualizada: {$nombre} ({$codigo})");
                        }
                    } catch (\Exception $e) {
                        $errores[] = "UPS '{$nombre}' ({$codigo}): {$e->getMessage()}";
                        $this->command->error("❌ Error al crear UPS '{$nombre}': {$e->getMessage()}");
                    }
                }

                DB::commit();

                // Resumen
                $this->command->newLine();
                $this->command->info("=" . str_repeat("=", 60));
                $this->command->info("📊 RESUMEN DE IMPORTACIÓN");
                $this->command->info("=" . str_repeat("=", 60));
                $this->command->info("✓ UPS creadas: {$upsCreadas}");
                $this->command->info("↻ UPS actualizadas: {$upsActualizadas}");
                $this->command->info("✓ Pisos creados: {$pisosCreados}");

                if (!empty($errores)) {
                    $this->command->newLine();
                    $this->command->warn("⚠ ERRORES ENCONTRADOS (" . count($errores) . "):");
                    foreach (array_slice($errores, 0, 20) as $error) {
                        $this->command->error("  • {$error}");
                    }
                    if (count($errores) > 20) {
                        $this->command->warn("  ... y " . (count($errores) - 20) . " errores más");
                    }
                } else {
                    $this->command->newLine();
                    $this->command->info("✅ Importación completada sin errores!");
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("❌ Error durante la importación: {$e->getMessage()}");
                $this->command->error("Stack trace: " . $e->getTraceAsString());
                throw $e;
            }

        } catch (\Exception $e) {
            $this->command->error("❌ Error al leer el archivo Excel: {$e->getMessage()}");
            throw $e;
        }
    }
}
