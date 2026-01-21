<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanUsersSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para limpiar la tabla de usuarios y sus relaciones.
     * 
     * ADVERTENCIA: Este seeder eliminará TODOS los usuarios y sus datos relacionados.
     * Úsalo con precaución en producción.
     * 
     * Para ejecutar en producción:
     * php artisan db:seed --class=CleanUsersSeeder
     * 
     * O con confirmación forzada (útil en scripts):
     * php artisan db:seed --class=CleanUsersSeeder --force
     */
    public function run(): void
    {
        // Verificar si estamos en producción y solicitar confirmación
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ADVERTENCIA: Estás ejecutando esto en PRODUCCIÓN');
            $this->command->warn('Se eliminarán TODOS los usuarios y sus datos relacionados.');
            $this->command->warn('');
            
            // Verificar si hay usuarios en la base de datos
            $userCount = DB::table('users')->count();
            if ($userCount > 0) {
                $this->command->info("Se encontraron {$userCount} usuarios en la base de datos.");
                $this->command->warn('');
            }
            
            if (!$this->command->confirm('¿Estás seguro de que deseas continuar?', false)) {
                $this->command->info('Operación cancelada.');
                return;
            }
        }

        $this->command->info('🧹 Iniciando limpieza de usuarios...');
        $this->command->info('');

        try {
            // Desactivar restricciones de foreign keys temporalmente
            // Laravel maneja esto automáticamente según el driver de BD
            Schema::disableForeignKeyConstraints();

            // Tablas relacionadas que dependen de usuarios (orden importante)
            $tables = [
                'accesos_registrados' => 'accesos registrados',
                'codigos_qr' => 'códigos QR',
                'codigo_qr_puerta_acceso' => 'relaciones código QR - puerta',
                'tarjetas_nfc' => 'tarjetas NFC',
                'tarjeta_nfc_puerta_acceso' => 'relaciones tarjeta NFC - puerta',
                'tarjeta_nfc_asignacions' => 'asignaciones de tarjetas NFC',
                'visitas_registradas' => 'visitas registradas',
                'visita_puerta' => 'relaciones visita - puerta',
                'user_documentos' => 'documentos de usuarios',
            ];

            $totalDeleted = 0;

            // Limpiar tablas relacionadas
            foreach ($tables as $table => $nombre) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    if ($count > 0) {
                        DB::table($table)->truncate();
                        $this->command->info("✓ Limpiados {$count} registros de {$nombre} ({$table})");
                        $totalDeleted += $count;
                    }
                }
            }

            // Limpiar tabla de usuarios (última porque otras dependen de ella)
            if (Schema::hasTable('users')) {
                $count = DB::table('users')->count();
                if ($count > 0) {
                    DB::table('users')->truncate();
                    $this->command->info("✓ Limpiados {$count} registros de usuarios (users)");
                    $totalDeleted += $count;
                }
            }

            // Reactivar restricciones de foreign keys
            Schema::enableForeignKeyConstraints();

            $this->command->info('');
            $this->command->info("✅ Limpieza completada exitosamente. Total de registros eliminados: {$totalDeleted}");
            $this->command->info('');
            $this->command->info('Ahora puedes ejecutar tu seeder de usuarios para recargar los datos:');
            $this->command->info('');
            $this->command->info('  php artisan db:seed --class=ImportarUsuariosSeeder');
            $this->command->info('  o');
            $this->command->info('  php artisan db:seed --class=SuperUserSeeder');
            $this->command->info('  o');
            $this->command->info('  php artisan db:seed');
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
