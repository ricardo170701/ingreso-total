<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanCargosSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para limpiar los cargos.
     * 
     * Elimina todos los cargos excepto "super user".
     * 
     * ADVERTENCIA: Este seeder eliminará TODOS los cargos excepto "super user".
     * Los usuarios asociados a cargos eliminados quedarán con cargo_id = null.
     * 
     * Para ejecutar:
     * php artisan db:seed --class=CleanCargosSeeder
     */
    public function run(): void
    {
        // Verificar si estamos en producción y solicitar confirmación
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ADVERTENCIA: Estás ejecutando esto en PRODUCCIÓN');
            $this->command->warn('Se eliminarán TODOS los cargos excepto "super user".');
            $this->command->warn('');
            
            // Mostrar cargos existentes
            $cargos = Cargo::query()->orderBy('name')->get();
            if ($cargos->isNotEmpty()) {
                $this->command->info('Cargos existentes en la base de datos:');
                foreach ($cargos as $cargo) {
                    $userCount = $cargo->users()->count();
                    $permisosCount = $cargo->permissions()->count();
                    $pisosCount = $cargo->pisos()->count();
                    $this->command->info("  • {$cargo->name} ({$userCount} usuarios, {$permisosCount} permisos, {$pisosCount} pisos)");
                }
                $this->command->warn('');
            }
            
            if (!$this->command->confirm('¿Estás seguro de que deseas continuar?', false)) {
                $this->command->info('Operación cancelada.');
                return;
            }
        }

        $this->command->info('🧹 Iniciando limpieza de cargos...');
        $this->command->info('');

        try {
            // Buscar el cargo "super user" a preservar
            $cargoSuperUser = Cargo::query()
                ->where('name', 'super user')
                ->orWhere('name', 'Super User')
                ->orWhere('name', 'SuperUser')
                ->first();

            if ($cargoSuperUser) {
                $this->command->info("✓ Cargo a preservar encontrado: {$cargoSuperUser->name}");
            } else {
                $this->command->warn('⚠ No se encontró el cargo "super user"');
                $this->command->warn('Se eliminarán TODOS los cargos.');
                
                if (!$this->command->confirm('¿Deseas continuar de todos modos?', false)) {
                    $this->command->info('Operación cancelada.');
                    return;
                }
            }

            // Desactivar restricciones de foreign keys temporalmente
            Schema::disableForeignKeyConstraints();

            // Contadores
            $cargosEliminados = 0;
            $usuariosAfectados = 0;

            // Obtener todos los cargos excepto "super user"
            $cargosAEliminar = Cargo::query()
                ->when($cargoSuperUser, function ($query) use ($cargoSuperUser) {
                    $query->where('id', '!=', $cargoSuperUser->id);
                })
                ->get();

            if ($cargosAEliminar->isEmpty()) {
                $this->command->info('ℹ No hay cargos para eliminar.');
                Schema::enableForeignKeyConstraints();
                return;
            }

            // Mostrar cargos que se van a eliminar
            $this->command->info('');
            $this->command->info('Cargos que serán eliminados:');
            foreach ($cargosAEliminar as $cargo) {
                $userCount = $cargo->users()->count();
                $permisosCount = $cargo->permissions()->count();
                $pisosCount = $cargo->pisos()->count();
                $puertasCount = $cargo->puertas()->count();
                $this->command->info("  • {$cargo->name} ({$userCount} usuarios, {$permisosCount} permisos, {$pisosCount} pisos, {$puertasCount} puertas)");
                $usuariosAfectados += $userCount;
            }

            $this->command->info('');
            $this->command->info("Total: {$cargosAEliminar->count()} cargos, {$usuariosAfectados} usuarios afectados");

            // Eliminar relaciones primero
            $cargoIds = $cargosAEliminar->pluck('id');
            
            // Eliminar relaciones cargo-permiso (cascadeOnDelete debería hacerlo automáticamente)
            $permisosRelaciones = DB::table('cargo_permission')
                ->whereIn('cargo_id', $cargoIds)
                ->count();

            if ($permisosRelaciones > 0) {
                DB::table('cargo_permission')->whereIn('cargo_id', $cargoIds)->delete();
                $this->command->info("✓ Eliminadas {$permisosRelaciones} relaciones cargo-permiso");
            }

            // Eliminar relaciones cargo-piso (cascadeOnDelete debería hacerlo automáticamente)
            $pisosRelaciones = DB::table('cargo_piso_acceso')
                ->whereIn('cargo_id', $cargoIds)
                ->count();

            if ($pisosRelaciones > 0) {
                DB::table('cargo_piso_acceso')->whereIn('cargo_id', $cargoIds)->delete();
                $this->command->info("✓ Eliminadas {$pisosRelaciones} relaciones cargo-piso");
            }

            // Eliminar relaciones cargo-puerta (cascadeOnDelete debería hacerlo automáticamente)
            $puertasRelaciones = DB::table('cargo_puerta_acceso')
                ->whereIn('cargo_id', $cargoIds)
                ->count();

            if ($puertasRelaciones > 0) {
                DB::table('cargo_puerta_acceso')->whereIn('cargo_id', $cargoIds)->delete();
                $this->command->info("✓ Eliminadas {$puertasRelaciones} relaciones cargo-puerta");
            }

            // Actualizar usuarios: establecer cargo_id = null para usuarios con cargos que se eliminarán
            // (aunque nullOnDelete debería hacerlo automáticamente, lo hacemos explícito para registro)
            $usuariosActualizados = DB::table('users')
                ->whereIn('cargo_id', $cargoIds)
                ->update(['cargo_id' => null]);

            if ($usuariosActualizados > 0) {
                $this->command->info("✓ Actualizados {$usuariosActualizados} usuarios (cargo_id establecido en null)");
            }

            // Eliminar los cargos
            foreach ($cargosAEliminar as $cargo) {
                $cargo->delete();
                $cargosEliminados++;
                $this->command->info("✓ Cargo eliminado: {$cargo->name}");
            }

            // Reactivar restricciones de foreign keys
            Schema::enableForeignKeyConstraints();

            // Resumen
            $this->command->newLine();
            $this->command->info("=" . str_repeat("=", 60));
            $this->command->info("📊 RESUMEN DE LIMPIEZA");
            $this->command->info("=" . str_repeat("=", 60));
            $this->command->info("✓ Cargos eliminados: {$cargosEliminados}");
            $this->command->info("✓ Relaciones cargo-permiso eliminadas: {$permisosRelaciones}");
            $this->command->info("✓ Relaciones cargo-piso eliminadas: {$pisosRelaciones}");
            $this->command->info("✓ Relaciones cargo-puerta eliminadas: {$puertasRelaciones}");
            $this->command->info("✓ Usuarios afectados (cargo_id = null): {$usuariosActualizados}");

            if ($cargoSuperUser) {
                $this->command->info("✓ Cargo preservado: {$cargoSuperUser->name}");
                $usuariosSuperUser = $cargoSuperUser->users()->count();
                $permisosSuperUser = $cargoSuperUser->permissions()->count();
                $this->command->info("  • Usuarios con este cargo: {$usuariosSuperUser}");
                $this->command->info("  • Permisos asignados: {$permisosSuperUser}");
            }

            // Mostrar cargos restantes
            $cargosRestantes = Cargo::query()->orderBy('name')->get();
            if ($cargosRestantes->isNotEmpty()) {
                $this->command->newLine();
                $this->command->info('Cargos restantes en el sistema:');
                foreach ($cargosRestantes as $cargo) {
                    $userCount = $cargo->users()->count();
                    $permisosCount = $cargo->permissions()->count();
                    $this->command->info("  • {$cargo->name} ({$userCount} usuarios, {$permisosCount} permisos)");
                }
            }

            $this->command->newLine();
            $this->command->info('✅ Limpieza completada exitosamente.');
            $this->command->info('');
            $this->command->warn('⚠ NOTA: Los usuarios que tenían cargos eliminados ahora tienen cargo_id = null.');
            $this->command->warn('Asegúrate de asignar cargos apropiados a estos usuarios si es necesario.');
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
