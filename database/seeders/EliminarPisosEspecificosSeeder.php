<?php

namespace Database\Seeders;

use App\Models\Piso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EliminarPisosEspecificosSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para eliminar pisos específicos.
     * 
     * Este seeder elimina los siguientes pisos:
     * - SOTANO
     * - ALMACEN
     * - SECRETARIA DE SALUD
     * - MIGRACION
     * 
     * ADVERTENCIA: Se eliminarán los pisos y sus relaciones asociadas:
     * - Las relaciones en cargo_piso_acceso se eliminarán automáticamente (cascade)
     * - Las referencias en puertas, ups, secretarias, dependencias, departamentos se establecerán a null
     * 
     * Para ejecutar:
     * php artisan db:seed --class=EliminarPisosEspecificosSeeder
     */
    public function run(): void
    {
        $nombresPisosAEliminar = [
            'SOTANO',
            'ALMACEN',
            'SECRETARIA DE SALUD',
            'MIGRACION',
        ];

        $this->command->info('🗑️  Iniciando eliminación de pisos específicos...');
        $this->command->info('');

        // Buscar los pisos a eliminar (búsqueda insensible a mayúsculas/minúsculas)
        $pisosEncontrados = [];
        $pisosNoEncontrados = [];

        foreach ($nombresPisosAEliminar as $nombre) {
            $piso = Piso::query()
                ->whereRaw('UPPER(nombre) = ?', [strtoupper($nombre)])
                ->first();

            if ($piso) {
                $pisosEncontrados[] = $piso;
            } else {
                $pisosNoEncontrados[] = $nombre;
            }
        }

        // Mostrar información de los pisos encontrados
        if (!empty($pisosNoEncontrados)) {
            $this->command->warn('⚠️  Pisos no encontrados:');
            foreach ($pisosNoEncontrados as $nombre) {
                $this->command->warn("  • {$nombre}");
            }
            $this->command->info('');
        }

        if (empty($pisosEncontrados)) {
            $this->command->info('✅ No se encontraron pisos para eliminar.');
            return;
        }

        $this->command->info('📋 Pisos encontrados que serán eliminados:');
        foreach ($pisosEncontrados as $piso) {
            // Contar relaciones
            $puertasCount = DB::table('puertas')->where('piso_id', $piso->id)->count();
            $upsCount = DB::table('ups')->where('piso_id', $piso->id)->count();
            $secretariasCount = DB::table('secretarias')->where('piso_id', $piso->id)->count();
            $dependenciasCount = DB::table('dependencias')->where('piso_id', $piso->id)->count();
            $departamentosCount = DB::table('departamentos')->where('piso_id', $piso->id)->count();
            $cargosPisoCount = DB::table('cargo_piso_acceso')->where('piso_id', $piso->id)->count();

            $this->command->info("  • {$piso->nombre} (ID: {$piso->id})");
            $this->command->comment("    → Puertas: {$puertasCount}");
            $this->command->comment("    → UPS: {$upsCount}");
            $this->command->comment("    → Secretarías: {$secretariasCount}");
            $this->command->comment("    → Dependencias: {$dependenciasCount}");
            $this->command->comment("    → Departamentos: {$departamentosCount}");
            $this->command->comment("    → Relaciones con cargos: {$cargosPisoCount}");
        }
        $this->command->info('');

        // Verificar si estamos en producción y solicitar confirmación
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ADVERTENCIA: Estás ejecutando esto en PRODUCCIÓN');
            $this->command->warn('Se eliminarán ' . count($pisosEncontrados) . ' piso(s) y sus relaciones asociadas.');
            $this->command->warn('');

            if (!$this->command->confirm('¿Estás seguro de que deseas continuar?', false)) {
                $this->command->info('Operación cancelada.');
                return;
            }
        }

        // Iniciar transacción
        DB::beginTransaction();

        try {
            $eliminados = 0;

            foreach ($pisosEncontrados as $piso) {
                // Obtener información antes de eliminar
                $nombrePiso = $piso->nombre;
                
                // Eliminar el piso (las relaciones se manejarán automáticamente por las foreign keys)
                $piso->delete();
                
                $eliminados++;
                $this->command->info("✓ Piso eliminado: {$nombrePiso}");
            }

            DB::commit();

            $this->command->info('');
            $this->command->info('=' . str_repeat('=', 60));
            $this->command->info('✅ RESUMEN');
            $this->command->info('=' . str_repeat('=', 60));
            $this->command->info("✓ Pisos eliminados: {$eliminados}");
            if (!empty($pisosNoEncontrados)) {
                $this->command->warn('⚠️  Pisos no encontrados: ' . count($pisosNoEncontrados));
            }
            $this->command->info('');
            $this->command->info('✅ Proceso completado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('');
            $this->command->error('❌ Error al eliminar pisos:');
            $this->command->error($e->getMessage());
            $this->command->error('');
            $this->command->error('Stack trace:');
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }
}
