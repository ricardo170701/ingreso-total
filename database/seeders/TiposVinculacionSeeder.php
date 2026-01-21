<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class TiposVinculacionSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para crear los tipos de vinculación.
     * 
     * Los tipos de vinculación son roles que indican el tipo de usuario:
     * - servidor_publico: Servidor público (permisos por cargo)
     * - proveedor: Proveedor (mismas reglas que servidor público)
     * - visitante: Visitante (QR por correo / accesos embebidos)
     * 
     * Para ejecutar:
     * php artisan db:seed --class=TiposVinculacionSeeder
     */
    public function run(): void
    {
        $this->command->info('📋 Creando tipos de vinculación...');
        $this->command->newLine();

        // Tipos de vinculación del sistema
        $tiposVinculacion = [
            [
                'name' => 'servidor_publico',
                'description' => 'Servidor público (permisos por cargo)',
            ],
            [
                'name' => 'proveedor',
                'description' => 'Proveedor (mismas reglas que servidor público)',
            ],
            [
                'name' => 'visitante',
                'description' => 'Visitante (QR por correo / accesos embebidos)',
            ],
        ];

        $creados = 0;
        $actualizados = 0;

        foreach ($tiposVinculacion as $tipoVinculacion) {
            $role = Role::query()->updateOrCreate(
                ['name' => $tipoVinculacion['name']],
                ['description' => $tipoVinculacion['description']]
            );

            if ($role->wasRecentlyCreated) {
                $creados++;
                $this->command->info("✓ Tipo de vinculación creado: {$tipoVinculacion['name']}");
            } else {
                $actualizados++;
                $this->command->comment("↻ Tipo de vinculación actualizado: {$tipoVinculacion['name']}");
            }
        }

        // Resumen
        $this->command->newLine();
        $this->command->info("=" . str_repeat("=", 60));
        $this->command->info("📊 RESUMEN");
        $this->command->info("=" . str_repeat("=", 60));
        $this->command->info("✓ Tipos de vinculación creados: {$creados}");
        $this->command->info("↻ Tipos de vinculación actualizados: {$actualizados}");
        $this->command->newLine();

        // Mostrar tipos de vinculación existentes
        $roles = Role::query()
            ->whereIn('name', ['servidor_publico', 'proveedor', 'visitante'])
            ->orderBy('name')
            ->get();

        if ($roles->isNotEmpty()) {
            $this->command->info('Tipos de vinculación en el sistema:');
            foreach ($roles as $role) {
                $userCount = $role->users()->count();
                $this->command->info("  • {$role->name} - {$role->description} ({$userCount} usuarios)");
            }
            $this->command->newLine();
        }

        $this->command->info('✅ Tipos de vinculación creados/actualizados exitosamente.');
    }
}
