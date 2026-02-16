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

class ThEducacionUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Importa usuarios desde el archivo Excel "TH_Educacion_F.xlsx".
     *
     * Ubicación del archivo:
     * - Por defecto: raíz del proyecto (TH_Educacion_F.xlsx)
     * - O define en .env: TH_EDUCACION_EXCEL_PATH=c:\Users\Ricardo\Downloads\TH_Educacion_F.xlsx
     *
     * Columnas esperadas (mismas que ImportarUsuariosSeeder):
     * - Nombre, Apellido, Email, Cedula, Observaciones, Contraseña
     * - Tipo de Vinculacion (mapea a role_id)
     * - Rol (mapea a cargo_id), Cargo (cargo_texto), Secretaria, Gerencia, Tipo de contrato
     */
    public function run(): void
    {
        $archivoExcel = env('TH_EDUCACION_EXCEL_PATH', base_path('TH_Educacion_F.xlsx'));

        if (!file_exists($archivoExcel)) {
            $this->command->error("❌ No se encontró el archivo Excel: {$archivoExcel}");
            $this->command->info("Opciones:");
            $this->command->info("  1. Copia 'TH_Educacion_F.xlsx' a la raíz del proyecto.");
            $this->command->info("  2. O en .env define: TH_EDUCACION_EXCEL_PATH=ruta\\completa\\al\\archivo.xlsx");
            return;
        }

        $this->command->info("📋 Importando usuarios desde TH_Educacion_F.xlsx...");
        $this->command->info("📂 Archivo: {$archivoExcel}");
        $this->command->newLine();

        try {
            $datos = Excel::toArray([], $archivoExcel);

            if (empty($datos) || empty($datos[0])) {
                $this->command->error("❌ El archivo Excel está vacío o no tiene datos.");
                return;
            }

            $filas = $datos[0];
            $encabezados = array_map('trim', array_map('strtolower', $filas[0]));
            $this->command->info("📊 Encabezados: " . implode(', ', $encabezados));
            $this->command->newLine();

            $indices = [];
            foreach ($encabezados as $index => $encabezado) {
                $encabezadoLimpio = Str::slug($encabezado, '_');
                $indices[$encabezadoLimpio] = $index;
            }

            $encabezadosRequeridos = [
                'nombre' => ['nombre'],
                'apellido' => ['apellido'],
                'email' => ['email', 'correo'],
                'cedula' => ['cedula', 'n_identidad'],
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
                    return;
                }
            }

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
            $idxCedula = $getIndex(['cedula', 'n_identidad']);
            $idxObservaciones = $getIndex(['observaciones']);
            $idxContraseña = $getIndex(['contraseña', 'contrasena', 'password']);
            $idxTipoVinculacion = $getIndex(['tipo_de_vinculacion', 'tipo_vinculacion', 'tipo-de-vinculacion']);
            $idxRol = $getIndex(['rol', 'role']);
            $idxCargo = $getIndex(['cargo']);
            $idxSecretaria = $getIndex(['secretaria']);
            $idxGerencia = $getIndex(['gerencia']);
            $idxTipoContrato = $getIndex(['tipo_de_contrato', 'tipo_contrato', 'tipo-de-contrato']);

            $usuariosCreados = 0;
            $usuariosActualizados = 0;
            $cargosCreados = 0;
            $secretariasCreadas = 0;
            $gerenciasCreadas = 0;
            $errores = [];

            $mapeoTipoVinculacion = [
                'servidor_publico' => 'servidor_publico',
                'servidor público' => 'servidor_publico',
                'funcionario' => 'servidor_publico',
                'proveedor' => 'proveedor',
                'contratista' => 'proveedor',
                'visitante' => 'visitante',
            ];

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
                for ($i = 1; $i < count($filas); $i++) {
                    $fila = $filas[$i];

                    $nombre = isset($fila[$idxNombre]) ? trim((string) $fila[$idxNombre]) : '';
                    $apellido = isset($fila[$idxApellido]) ? trim((string) $fila[$idxApellido]) : '';
                    $email = isset($fila[$idxEmail]) ? trim((string) $fila[$idxEmail]) : '';
                    $cedula = isset($fila[$idxCedula]) ? trim((string) $fila[$idxCedula]) : '';
                    $observaciones = isset($fila[$idxObservaciones]) ? trim((string) $fila[$idxObservaciones]) : null;
                    $contraseña = isset($fila[$idxContraseña]) ? trim((string) $fila[$idxContraseña]) : '';
                    $tipoVinculacion = isset($fila[$idxTipoVinculacion]) ? trim((string) $fila[$idxTipoVinculacion]) : '';
                    $rolNombre = isset($fila[$idxRol]) ? trim((string) $fila[$idxRol]) : '';
                    $cargoTexto = isset($fila[$idxCargo]) ? trim((string) $fila[$idxCargo]) : '';
                    $secretariaNombre = isset($fila[$idxSecretaria]) ? trim((string) $fila[$idxSecretaria]) : '';
                    $gerenciaNombre = isset($fila[$idxGerencia]) ? trim((string) $fila[$idxGerencia]) : '';
                    $tipoContrato = isset($fila[$idxTipoContrato]) ? trim((string) $fila[$idxTipoContrato]) : null;

                    if (empty($email) || empty($cedula)) {
                        $errores[] = "Fila " . ($i + 1) . ": Email o cédula vacío";
                        continue;
                    }

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errores[] = "Fila " . ($i + 1) . ": Email inválido: {$email}";
                        continue;
                    }

                    $nombreRol = null;
                    if (!empty($tipoVinculacion)) {
                        $tipoVinculacionLimpio = Str::slug(strtolower($tipoVinculacion), '_');
                        $nombreRol = $mapeoTipoVinculacion[$tipoVinculacionLimpio]
                            ?? $mapeoTipoVinculacion[strtolower($tipoVinculacion)]
                            ?? null;
                    }
                    if (!$nombreRol) {
                        $nombreRol = 'servidor_publico';
                    }

                    $role = Role::query()->where('name', $nombreRol)->first();
                    if (!$role) {
                        $errores[] = "Fila " . ($i + 1) . ": Tipo de vinculación '{$nombreRol}' no existe. Ejecuta: php artisan db:seed --class=AccessControlSeeder";
                        continue;
                    }

                    $cargo = null;
                    if (!empty($rolNombre)) {
                        $cargo = Cargo::query()->firstOrCreate(
                            ['name' => $rolNombre],
                            ['name' => $rolNombre, 'description' => null, 'activo' => true]
                        );
                        if ($cargo->wasRecentlyCreated) {
                            $cargosCreados++;
                            $permisosIngreso = Permission::query()
                                ->whereIn('name', ['view_ingreso', 'create_ingreso'])
                                ->pluck('id');
                            if ($permisosIngreso->isNotEmpty()) {
                                $cargo->permissions()->sync($permisosIngreso);
                            }
                            $piso1 = Piso::query()
                                ->where('nombre', 'Piso 1')
                                ->orWhere('id', 1)
                                ->first();
                            if ($piso1) {
                                $cargo->pisos()->sync([
                                    $piso1->id => [
                                        'hora_inicio' => null,
                                        'hora_fin' => null,
                                        'dias_semana' => '1,2,3,4,5,6,7',
                                        'fecha_inicio' => null,
                                        'fecha_fin' => null,
                                        'activo' => true,
                                    ],
                                ]);
                            }
                        }
                    }

                    $gerenciaId = null;
                    if (!empty($secretariaNombre)) {
                        $secretaria = Secretaria::query()->firstOrCreate(
                            ['nombre' => $secretariaNombre],
                            ['nombre' => $secretariaNombre, 'activo' => true]
                        );
                        if ($secretaria->wasRecentlyCreated) {
                            $secretariasCreadas++;
                        }
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

                    $tipoContratoNormalizado = null;
                    if ($tipoContrato) {
                        $tipoContratoLimpio = Str::slug(strtolower($tipoContrato), '_');
                        $tipoContratoNormalizado = $mapeoTipoContrato[$tipoContratoLimpio]
                            ?? $mapeoTipoContrato[strtolower($tipoContrato)]
                            ?? $tipoContrato;
                    }

                    $nombreCompleto = trim("{$nombre} {$apellido}");
                    $password = !empty($contraseña) ? $contraseña : $cedula;

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
                                'role_id' => $role->id,
                                'cargo_id' => $cargo?->id,
                                'cargo_texto' => $cargoTexto ?: null,
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

                $this->command->newLine();
                $this->command->info("=" . str_repeat("=", 60));
                $this->command->info("📊 RESUMEN TH_Educacion_F.xlsx");
                $this->command->info("=" . str_repeat("=", 60));
                $this->command->info("✓ Usuarios creados: {$usuariosCreados}");
                $this->command->info("↻ Usuarios actualizados: {$usuariosActualizados}");
                $this->command->info("✓ Cargos creados: {$cargosCreados}");
                $this->command->info("✓ Secretarías creadas: {$secretariasCreadas}");
                $this->command->info("✓ Gerencias creadas: {$gerenciasCreadas}");

                if (!empty($errores)) {
                    $this->command->newLine();
                    $this->command->warn("⚠ ERRORES (" . count($errores) . "):");
                    foreach (array_slice($errores, 0, 20) as $error) {
                        $this->command->error("  • {$error}");
                    }
                    if (count($errores) > 20) {
                        $this->command->warn("  ... y " . (count($errores) - 20) . " errores más");
                    }
                } else {
                    $this->command->newLine();
                    $this->command->info("✅ Importación completada sin errores.");
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("❌ Error durante la importación: {$e->getMessage()}");
                throw $e;
            }
        } catch (\Exception $e) {
            $this->command->error("❌ Error al leer el Excel: {$e->getMessage()}");
            throw $e;
        }
    }
}
