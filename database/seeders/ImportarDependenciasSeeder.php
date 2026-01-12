<?php

namespace Database\Seeders;

use App\Models\Gerencia;
use App\Models\Secretaria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportarDependenciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Importa secretarías y gerencias desde datos hardcodeados.
     */
    public function run(): void
    {
        $datos = [
            [
                'secretaria' => 'Despacho del(la) Gobernador(a)',
                'gerencias' => [
                    'Oficina de Protocolo',
                    'Oficina de Control Interno',
                    'Oficina de Control Disciplinario Interno',
                    'Dirección para el Fomento de la Educación Superior',
                    'Dirección para la Gestión del Riesgo de Desastres',
                ],
            ],
            [
                'secretaria' => 'Secretaría Privada',
                'gerencias' => [],
            ],
            [
                'secretaria' => 'Secretaría Jurídica',
                'gerencias' => [
                    'Gerencia de Asuntos Judiciales y Contencioso Administrativos',
                    'Gerencia de Asuntos Contractuales',
                    'Gerencia de Conceptos y Asistencia Jurídica Territorial',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Comunicaciones',
                'gerencias' => [
                    'Gerencia de Redes Sociales y Televisión',
                    'Gerencia de Radio',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Agricultura y Desarrollo Rural',
                'gerencias' => [
                    'Gerencia de Desarrollo Rural',
                    'Gerencia de Desarrollo Agropecuario',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Competitividad y Desarrollo Económico',
                'gerencias' => [
                    'Gerencia de Ciencia, Innovación y Cooperación',
                    'Gerencia de Industria, Empleo y Emprendimiento',
                ],
            ],
            [
                'secretaria' => 'Secretaría Social',
                'gerencias' => [
                    'Gerencia de Asuntos Étnicos',
                    'Gerencia de Infancia, Adolescencia y Juventud',
                    'Gerencia de Adulto Mayor y Personas en Condición de Discapacidad',
                    'Gerencia Plan de Alimentos y Nutrición',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Educación',
                'gerencias' => [
                    'Gerencia de Cobertura',
                    'Gerencia de Calidad Educativa',
                    'Gerencia Administrativa y Financiera',
                ],
            ],
            [
                'secretaria' => 'Secretaría de la Mujer, la Familia y la Equidad de Género',
                'gerencias' => [],
            ],
            [
                'secretaria' => 'Secretaría de Gobierno y Seguridad',
                'gerencias' => [
                    'Gerencia de Seguridad y Convivencia Ciudadana',
                    'Gerencia de Acción Comunal y Participación Ciudadana',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Ambiente',
                'gerencias' => [],
            ],
            [
                'secretaria' => 'Secretaría de Minas y Energía',
                'gerencias' => [],
            ],
            [
                'secretaria' => 'Secretaría de Tecnologías y Sistemas de Información',
                'gerencias' => [
                    'Gerencia de Infraestructura y Sistemas de Información',
                    'Gerencia de Gobierno Digital',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Salud',
                'gerencias' => [
                    'Gerencia de Prestación de Servicios de Salud',
                    'Gerencia de Promoción y Prevención',
                    'Gerencia de Calidad, Inspección y Vigilancia de los Servicios',
                    'Gerencia Administrativa de Salud',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Derechos Humanos y Paz',
                'gerencias' => [
                    'Gerencia de Víctimas',
                    'Gerencia de Promoción de Derechos Humanos',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Vivienda',
                'gerencias' => [],
            ],
            [
                'secretaria' => 'Secretaría Administrativa',
                'gerencias' => [
                    'Gerencia de Talento Humano',
                    'Gerencia de Servicios Administrativos',
                    'Gerencia de Servicio al Ciudadano y Gestión Documental',
                    'Gerencia de Desarrollo Organizacional',
                ],
            ],
            [
                'secretaria' => 'Secretaría de Hacienda',
                'gerencias' => [
                    'Gerencia de Presupuesto',
                    'Gerencia de Contaduría',
                    'Gerencia de Tesorería',
                    'Gerencia de Rentas',
                ],
            ],
            [
                'secretaria' => 'Departamento Administrativo de Planeación Departamental',
                'gerencias' => [
                    'Gerencia de Información y Estudios Económicos',
                    'Gerencia de Inversión Pública y Bancos de Proyectos',
                    'Gerencia de Desarrollo Regional',
                ],
            ],
        ];

        $this->command->info("📋 Iniciando importación de dependencias...");
        $this->command->newLine();

        $secretariasCreadas = 0;
        $secretariasActualizadas = 0;
        $gerenciasCreadas = 0;
        $gerenciasActualizadas = 0;
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($datos as $index => $item) {
                $nombreSecretaria = trim($item['secretaria']);

                if (empty($nombreSecretaria)) {
                    continue;
                }

                // Crear o actualizar la secretaría
                $secretaria = Secretaria::query()->updateOrCreate(
                    ['nombre' => $nombreSecretaria],
                    [
                        'nombre' => $nombreSecretaria,
                        'activo' => true,
                    ]
                );

                if ($secretaria->wasRecentlyCreated) {
                    $secretariasCreadas++;
                    $this->command->info("✓ Secretaría creada: {$nombreSecretaria}");
                } else {
                    $secretariasActualizadas++;
                    $this->command->comment("↻ Secretaría actualizada: {$nombreSecretaria}");
                }

                // Procesar las gerencias
                foreach ($item['gerencias'] as $nombreGerencia) {
                    $nombreGerencia = trim($nombreGerencia);

                    if (empty($nombreGerencia)) {
                        continue;
                    }

                    try {
                        $gerencia = Gerencia::query()->updateOrCreate(
                            [
                                'secretaria_id' => $secretaria->id,
                                'nombre' => $nombreGerencia,
                            ],
                            [
                                'secretaria_id' => $secretaria->id,
                                'nombre' => $nombreGerencia,
                                'activo' => true,
                            ]
                        );

                        if ($gerencia->wasRecentlyCreated) {
                            $gerenciasCreadas++;
                            $this->command->line("  └─ ✓ Gerencia creada: {$nombreGerencia}");
                        } else {
                            $gerenciasActualizadas++;
                            $this->command->line("  └─ ↻ Gerencia actualizada: {$nombreGerencia}");
                        }
                    } catch (\Exception $e) {
                        $errores[] = "Secretaría '{$nombreSecretaria}', Gerencia '{$nombreGerencia}': {$e->getMessage()}";
                        $this->command->error("  └─ ❌ Error al crear gerencia '{$nombreGerencia}': {$e->getMessage()}");
                    }
                }
            }

            DB::commit();

            // Resumen
            $this->command->newLine();
            $this->command->info("=" . str_repeat("=", 60));
            $this->command->info("📊 RESUMEN DE IMPORTACIÓN");
            $this->command->info("=" . str_repeat("=", 60));
            $this->command->info("✓ Secretarías creadas: {$secretariasCreadas}");
            $this->command->info("↻ Secretarías actualizadas: {$secretariasActualizadas}");
            $this->command->info("✓ Gerencias creadas: {$gerenciasCreadas}");
            $this->command->info("↻ Gerencias actualizadas: {$gerenciasActualizadas}");

            if (!empty($errores)) {
                $this->command->newLine();
                $this->command->warn("⚠ ERRORES ENCONTRADOS (" . count($errores) . "):");
                foreach ($errores as $error) {
                    $this->command->error("  • {$error}");
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
    }
}
