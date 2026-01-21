<?php

namespace Database\Seeders;

use App\Models\Gerencia;
use App\Models\Secretaria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanSecretariasSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para limpiar todas las secretarías.
     * 
     * ADVERTENCIA: Este seeder eliminará TODAS las secretarías y sus gerencias asociadas.
     * Los usuarios asociados a gerencias eliminadas quedarán con gerencia_id = null.
     * 
     * Para ejecutar:
     * php artisan db:seed --class=CleanSecretariasSeeder
     */
    public function run(): void
    {
        // Verificar si estamos en producción y solicitar confirmación
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ADVERTENCIA: Estás ejecutando esto en PRODUCCIÓN');
            $this->command->warn('Se eliminarán TODAS las secretarías y sus gerencias asociadas.');
            $this->command->warn('');
            
            // Mostrar secretarías existentes
            $secretarias = Secretaria::query()->orderBy('nombre')->get();
            if ($secretarias->isNotEmpty()) {
                $this->command->info('Secretarías existentes en la base de datos:');
                foreach ($secretarias as $secretaria) {
                    $gerenciasCount = $secretaria->gerencias()->count();
                    $usuariosCount = $secretaria->users()->count();
                    $this->command->info("  • {$secretaria->nombre} ({$gerenciasCount} gerencias, {$usuariosCount} usuarios)");
                }
                $this->command->warn('');
            }
            
            if (!$this->command->confirm('¿Estás seguro de que deseas continuar?', false)) {
                $this->command->info('Operación cancelada.');
                return;
            }
        }

        $this->command->info('🧹 Iniciando limpieza de secretarías...');
        $this->command->info('');

        try {
            // Desactivar restricciones de foreign keys temporalmente
            Schema::disableForeignKeyConstraints();

            // Contadores
            $secretariasEliminadas = 0;
            $gerenciasEliminadas = 0;
            $usuariosAfectados = 0;
            $codigosQrAfectados = 0;
            $tarjetasNfcAfectadas = 0;

            // Obtener todas las secretarías
            $secretariasAEliminar = Secretaria::query()->get();

            if ($secretariasAEliminar->isEmpty()) {
                $this->command->info('ℹ No hay secretarías para eliminar.');
                Schema::enableForeignKeyConstraints();
                return;
            }

            // Mostrar secretarías que se van a eliminar
            $this->command->info('Secretarías que serán eliminadas:');
            foreach ($secretariasAEliminar as $secretaria) {
                $gerenciasCount = $secretaria->gerencias()->count();
                $usuariosCount = $secretaria->users()->count();
                $this->command->info("  • {$secretaria->nombre} ({$gerenciasCount} gerencias, {$usuariosCount} usuarios)");
                
                // Contar relaciones en gerencias antes de eliminarlas
                foreach ($secretaria->gerencias as $gerencia) {
                    $gerenciasEliminadas++;
                    $usuariosAfectados += $gerencia->users()->count();
                    $codigosQrAfectados += $gerencia->codigosQr()->count();
                    $tarjetasNfcAfectadas += DB::table('tarjetas_nfc')->where('gerencia_id', $gerencia->id)->count();
                }
            }

            $this->command->info('');
            $this->command->info("Total: {$secretariasAEliminar->count()} secretarías, {$gerenciasEliminadas} gerencias que serán eliminadas");

            // Obtener IDs de gerencias que se eliminarán (por cascadeOnDelete)
            $gerenciaIds = Gerencia::query()
                ->whereIn('secretaria_id', $secretariasAEliminar->pluck('id'))
                ->pluck('id');

            // Actualizar usuarios: establecer gerencia_id = null para usuarios con gerencias que se eliminarán
            // (aunque nullOnDelete debería hacerlo automáticamente, lo hacemos explícito para registro)
            if ($gerenciaIds->isNotEmpty()) {
                $usuariosActualizados = DB::table('users')
                    ->whereIn('gerencia_id', $gerenciaIds)
                    ->update(['gerencia_id' => null]);

                if ($usuariosActualizados > 0) {
                    $this->command->info("✓ Actualizados {$usuariosActualizados} usuarios (gerencia_id establecido en null)");
                }

                // Actualizar códigos QR: establecer gerencia_id = null
                $codigosQrActualizados = DB::table('codigos_qr')
                    ->whereIn('gerencia_id', $gerenciaIds)
                    ->update(['gerencia_id' => null]);

                if ($codigosQrActualizados > 0) {
                    $this->command->info("✓ Actualizados {$codigosQrActualizados} códigos QR (gerencia_id establecido en null)");
                }

                // Actualizar tarjetas NFC: establecer gerencia_id = null
                $tarjetasNfcActualizadas = DB::table('tarjetas_nfc')
                    ->whereIn('gerencia_id', $gerenciaIds)
                    ->update(['gerencia_id' => null]);

                if ($tarjetasNfcActualizadas > 0) {
                    $this->command->info("✓ Actualizadas {$tarjetasNfcActualizadas} tarjetas NFC (gerencia_id establecido en null)");
                }
            }

            // Eliminar las secretarías (las gerencias se eliminarán automáticamente por cascadeOnDelete)
            foreach ($secretariasAEliminar as $secretaria) {
                $gerenciasCount = $secretaria->gerencias()->count();
                $secretaria->delete();
                $secretariasEliminadas++;
                $this->command->info("✓ Secretaría eliminada: {$secretaria->nombre} ({$gerenciasCount} gerencias eliminadas automáticamente)");
            }

            // Reactivar restricciones de foreign keys
            Schema::enableForeignKeyConstraints();

            // Resumen
            $this->command->newLine();
            $this->command->info("=" . str_repeat("=", 60));
            $this->command->info("📊 RESUMEN DE LIMPIEZA");
            $this->command->info("=" . str_repeat("=", 60));
            $this->command->info("✓ Secretarías eliminadas: {$secretariasEliminadas}");
            $this->command->info("✓ Gerencias eliminadas (automático): {$gerenciasEliminadas}");
            $this->command->info("✓ Usuarios afectados (gerencia_id = null): {$usuariosAfectados}");
            $this->command->info("✓ Códigos QR afectados (gerencia_id = null): {$codigosQrAfectados}");
            $this->command->info("✓ Tarjetas NFC afectadas (gerencia_id = null): {$tarjetasNfcAfectadas}");

            // Mostrar secretarías restantes (no debería haber ninguna)
            $secretariasRestantes = Secretaria::query()->count();
            if ($secretariasRestantes > 0) {
                $this->command->newLine();
                $this->command->warn("⚠ Aún quedan {$secretariasRestantes} secretarías en el sistema.");
            } else {
                $this->command->newLine();
                $this->command->info('✓ No quedan secretarías en el sistema.');
            }

            $this->command->newLine();
            $this->command->info('✅ Limpieza completada exitosamente.');
            $this->command->info('');
            $this->command->warn('⚠ NOTA: Los usuarios, códigos QR y tarjetas NFC que tenían gerencias eliminadas ahora tienen gerencia_id = null.');
            $this->command->warn('Asegúrate de asignar gerencias apropiadas a estos registros si es necesario.');
            $this->command->info('');

        } catch (\Exception $e) {
            // Asegurar que las restricciones se reactiven incluso si hay error
            Schema::enableForeignKeyConstraints();
            
            $this->command->error('');
            $this->command->error('❌ Error durante la limpieza:');
            $this->command->error($e->getMessage());
            $this->command->error('');
            $this->command->error('Stack trace:');
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }
}
