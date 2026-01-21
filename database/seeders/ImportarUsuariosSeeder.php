<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Gerencia;
use App\Models\Permission;
use App\Models\Piso;
use App\Models\Role;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ImportarUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Importa usuarios desde el archivo Excel "Datos TH_F.xlsx"
     *
     * Columnas esperadas:
     * - Nombre
     * - Apellido
     * - Email
     * - Cedula
     * - Observaciones
     * - Contraseña
     * - Tipo de Vinculacion (mapea a role_id: servidor_publico, proveedor, visitante)
     * - Rol (mapea a cargo_id - tabla cargos)
     * - Cargo (mapea a cargo_texto - campo texto de referencia en users)
     * - Secretaria
     * - Gerencia
     * - Tipo de contrato
     */
    public function run(): void
    {
        $archivoExcel = base_path('Datos TH_F.xlsx');

        if (!file_exists($archivoExcel)) {
            $this->command->error("❌ No se encontró el archivo Excel: {$archivoExcel}");
            $this->command->info("Asegúrate de que el archivo 'Datos TH_F.xlsx' esté en la raíz del proyecto.");
            return;
        }

        $this->command->info("📋 Iniciando importación de usuarios desde Excel...");
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
            $encabezados = array_map('trim', array_map('strtolower', $filas[0]));
            $this->command->info("📊 Encabezados encontrados: " . implode(', ', $encabezados));
            $this->command->newLine();

            // Mapeo de encabezados (flexible, puede variar el orden)
            $indices = [];
            foreach ($encabezados as $index => $encabezado) {
                $encabezadoLimpio = Str::slug($encabezado, '_');
                $indices[$encabezadoLimpio] = $index;
            }

            // Verificar que existan los encabezados necesarios
            $encabezadosRequeridos = [
                'nombre' => ['nombre'],
                'apellido' => ['apellido'],
                'email' => ['email', 'correo'],
                'cedula' => ['cedula', 'cedula', 'n_identidad'],
                'contraseña' => ['contraseña', 'contrasena', 'password'],
            ];

            foreach ($encabezadosRequeridos as $campo => $variantes) {
                $encontrado = false;
                foreach ($variantes as $variante) {
                    if (isset($indices[$variante])) {
                        $encontrado = true;
                        break;
                    }
                }
                if (!$encontrado) {
                    $this->command->error("❌ No se encontró el encabezado requerido: {$campo}");
                    $this->command->error("Variantes buscadas: " . implode(', ', $variantes));
                    return;
                }
            }

            // Obtener índices de columnas
            $getIndex = function ($variantes) use ($indices) {
                foreach ($variantes as $variante) {
                    if (isset($indices[$variante])) {
                        return $indices[$variante];
                    }
                }
                return null;
            };

            $idxNombre = $getIndex(['nombre']);
            $idxApellido = $getIndex(['apellido']);
            $idxEmail = $getIndex(['email', 'correo']);
            $idxCedula = $getIndex(['cedula', 'cedula', 'n_identidad']);
            $idxObservaciones = $getIndex(['observaciones']);
            $idxContraseña = $getIndex(['contraseña', 'contrasena', 'password']);
            $idxTipoVinculacion = $getIndex(['tipo_de_vinculacion', 'tipo_vinculacion', 'tipo-de-vinculacion']);
            $idxRol = $getIndex(['rol', 'role']); // Esta columna será el CARGO (tabla cargos)
            $idxCargo = $getIndex(['cargo']); // Esta columna será el cargo_texto (campo texto)
            $idxSecretaria = $getIndex(['secretaria']);
            $idxGerencia = $getIndex(['gerencia']);
            $idxTipoContrato = $getIndex(['tipo_de_contrato', 'tipo_contrato', 'tipo-de-contrato']);

            // Contadores
            $usuariosCreados = 0;
            $usuariosActualizados = 0;
            $cargosCreados = 0;
            $secretariasCreadas = 0;
            $gerenciasCreadas = 0;
            $errores = [];

            // Mapeo de tipos de vinculación a nombres de roles
            $mapeoTipoVinculacion = [
                'servidor_publico' => 'servidor_publico',
                'servidor público' => 'servidor_publico',
                'funcionario' => 'servidor_publico',
                'proveedor' => 'proveedor',
                'contratista' => 'proveedor',
                'visitante' => 'visitante',
            ];

            // Mapeo de tipos de contrato
            $mapeoTipoContrato = [
                'prestacion_servicios' => 'prestacion_servicios',
                'prestación de servicios' => 'prestacion_servicios',
                'contratista_externo' => 'contratista_externo',
                'contratista externo' => 'contratista_externo',
                'contrato_indefinido' => 'contrato_indefinido',
                'contrato indefinido' => 'contrato_indefinido',
            ];

            DB::beginTransaction();

            try {
                // Procesar cada fila (omitir la primera que son los encabezados)
                for ($i = 1; $i < count($filas); $i++) {
                    $fila = $filas[$i];

                    // Obtener valores de la fila
                    $nombre = isset($fila[$idxNombre]) ? trim($fila[$idxNombre]) : '';
                    $apellido = isset($fila[$idxApellido]) ? trim($fila[$idxApellido]) : '';
                    $email = isset($fila[$idxEmail]) ? trim($fila[$idxEmail]) : '';
                    $cedula = isset($fila[$idxCedula]) ? trim($fila[$idxCedula]) : '';
                    $observaciones = isset($fila[$idxObservaciones]) ? trim($fila[$idxObservaciones]) : null;
                    $contraseña = isset($fila[$idxContraseña]) ? trim($fila[$idxContraseña]) : '';
                    $tipoVinculacion = isset($fila[$idxTipoVinculacion]) ? trim($fila[$idxTipoVinculacion]) : '';
                    // La columna "Rol" del Excel es el CARGO (tabla cargos)
                    $rolNombre = isset($fila[$idxRol]) ? trim($fila[$idxRol]) : '';
                    // La columna "Cargo" del Excel es el cargo_texto (campo texto de referencia)
                    $cargoTexto = isset($fila[$idxCargo]) ? trim($fila[$idxCargo]) : '';
                    $secretariaNombre = isset($fila[$idxSecretaria]) ? trim($fila[$idxSecretaria]) : '';
                    $gerenciaNombre = isset($fila[$idxGerencia]) ? trim($fila[$idxGerencia]) : '';
                    $tipoContrato = isset($fila[$idxTipoContrato]) ? trim($fila[$idxTipoContrato]) : null;

                    // Validar campos requeridos
                    if (empty($email) || empty($cedula)) {
                        $errores[] = "Fila " . ($i + 1) . ": Email o cédula vacío";
                        continue;
                    }

                    // Validar email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errores[] = "Fila " . ($i + 1) . ": Email inválido: {$email}";
                        continue;
                    }

                    // Determinar el tipo de vinculación (role_id)
                    $nombreRol = null;
                    if (!empty($tipoVinculacion)) {
                        $tipoVinculacionLimpio = Str::slug(strtolower($tipoVinculacion), '_');
                        $nombreRol = $mapeoTipoVinculacion[$tipoVinculacionLimpio] ??
                            $mapeoTipoVinculacion[strtolower($tipoVinculacion)] ?? null;
                    }

                    // Si aún no hay rol, usar servidor_publico por defecto
                    if (!$nombreRol) {
                        $nombreRol = 'servidor_publico';
                    }

                    // Obtener el tipo de vinculación (Role)
                    $role = Role::query()->where('name', $nombreRol)->first();
                    if (!$role) {
                        $errores[] = "Fila " . ($i + 1) . ": Tipo de vinculación '{$nombreRol}' no existe. Ejecuta primero: php artisan db:seed --class=AccessControlSeeder";
                        continue;
                    }

                    // Crear o obtener el CARGO (la columna "Rol" del Excel se usa para el cargo_id)
                    $cargo = null;
                    if (!empty($rolNombre)) {
                        $cargo = Cargo::query()->firstOrCreate(
                            ['name' => $rolNombre],
                            [
                                'name' => $rolNombre,
                                'description' => null,
                                'activo' => true,
                            ]
                        );

                        if ($cargo->wasRecentlyCreated) {
                            $cargosCreados++;

                            // Asignar permisos por defecto: ver ingreso y generar código QR
                            $permisosIngreso = Permission::query()
                                ->whereIn('name', ['view_ingreso', 'create_ingreso'])
                                ->pluck('id');

                            if ($permisosIngreso->isNotEmpty()) {
                                $cargo->permissions()->sync($permisosIngreso);
                                $this->command->comment("  → Permisos asignados al cargo '{$rolNombre}': ver ingreso, generar código QR");
                            }

                            // Asignar acceso al Piso 1
                            $piso1 = Piso::query()
                                ->where('nombre', 'Piso 1')
                                ->orWhere('id', 1)
                                ->first();

                            if ($piso1) {
                                // Configuración del pivot para el piso
                                $pivotConfig = [
                                    'hora_inicio' => null, // Siempre disponible
                                    'hora_fin' => null,
                                    'dias_semana' => '1,2,3,4,5,6,7', // Todos los días
                                    'fecha_inicio' => null,
                                    'fecha_fin' => null,
                                    'activo' => true,
                                ];

                                $cargo->pisos()->sync([
                                    $piso1->id => $pivotConfig
                                ]);
                                $this->command->comment("  → Acceso al Piso 1 asignado al cargo '{$rolNombre}'");
                            } else {
                                $this->command->warn("  ⚠ No se encontró el Piso 1 para asignar al cargo '{$rolNombre}'");
                            }
                        }
                    }

                    // Crear o obtener la secretaría
                    $gerenciaId = null;
                    if (!empty($secretariaNombre)) {
                        $secretaria = Secretaria::query()->firstOrCreate(
                            ['nombre' => $secretariaNombre],
                            [
                                'nombre' => $secretariaNombre,
                                'activo' => true,
                            ]
                        );

                        if ($secretaria->wasRecentlyCreated) {
                            $secretariasCreadas++;
                        }

                        // Crear o obtener la gerencia (si se proporciona)
                        if (!empty($gerenciaNombre)) {
                            $gerencia = Gerencia::query()->firstOrCreate(
                                [
                                    'secretaria_id' => $secretaria->id,
                                    'nombre' => $gerenciaNombre,
                                ],
                                [
                                    'secretaria_id' => $secretaria->id,
                                    'nombre' => $gerenciaNombre,
                                    'activo' => true,
                                ]
                            );

                            if ($gerencia->wasRecentlyCreated) {
                                $gerenciasCreadas++;
                            }

                            $gerenciaId = $gerencia->id;
                        }
                    }

                    // Normalizar tipo de contrato
                    $tipoContratoNormalizado = null;
                    if ($tipoContrato) {
                        $tipoContratoLimpio = Str::slug(strtolower($tipoContrato), '_');
                        $tipoContratoNormalizado = $mapeoTipoContrato[$tipoContratoLimpio] ??
                            $mapeoTipoContrato[strtolower($tipoContrato)] ??
                            $tipoContrato;
                    }

                    // Crear nombre completo
                    $nombreCompleto = trim("{$nombre} {$apellido}");

                    // Si no hay contraseña, usar la cédula como contraseña por defecto
                    $password = !empty($contraseña) ? $contraseña : $cedula;

                    // Crear o actualizar el usuario
                    try {
                        $user = User::query()->updateOrCreate(
                            ['email' => $email],
                            [
                                'name' => $nombreCompleto ?: $email,
                                'nombre' => $nombre ?: null,
                                'apellido' => $apellido ?: null,
                                'email' => $email,
                                'n_identidad' => $cedula,
                                'password' => Hash::make($password),
                                'role_id' => $role->id, // Tipo de vinculación (servidor_publico, proveedor, visitante)
                                'cargo_id' => $cargo?->id, // La columna "Rol" del Excel se asigna aquí
                                'cargo_texto' => $cargoTexto ?: null, // La columna "Cargo" del Excel se asigna aquí
                                'gerencia_id' => $gerenciaId,
                                'observaciones' => $observaciones,
                                'tipo_contrato' => $tipoContratoNormalizado,
                                'activo' => true,
                                'es_discapacitado' => false,
                            ]
                        );

                        if ($user->wasRecentlyCreated) {
                            $usuariosCreados++;
                            $this->command->info("✓ Usuario creado: {$nombreCompleto} ({$email})");
                        } else {
                            $usuariosActualizados++;
                            $this->command->comment("↻ Usuario actualizado: {$nombreCompleto} ({$email})");
                        }
                    } catch (\Exception $e) {
                        $errores[] = "Usuario '{$nombreCompleto}' ({$email}): {$e->getMessage()}";
                        $this->command->error("❌ Error al crear usuario '{$nombreCompleto}': {$e->getMessage()}");
                    }
                }

                DB::commit();

                // Resumen
                $this->command->newLine();
                $this->command->info("=" . str_repeat("=", 60));
                $this->command->info("📊 RESUMEN DE IMPORTACIÓN");
                $this->command->info("=" . str_repeat("=", 60));
                $this->command->info("✓ Usuarios creados: {$usuariosCreados}");
                $this->command->info("↻ Usuarios actualizados: {$usuariosActualizados}");
                $this->command->info("✓ Cargos creados: {$cargosCreados}");
                $this->command->info("✓ Secretarías creadas: {$secretariasCreadas}");
                $this->command->info("✓ Gerencias creadas: {$gerenciasCreadas}");

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
