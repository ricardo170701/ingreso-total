<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class SuperUserCargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear o actualizar el cargo "super user"
        $cargo = Cargo::query()->updateOrCreate(
            ['name' => 'super user'],
            [
                'description' => 'Usuario con todos los permisos del sistema activos',
                'activo' => true,
            ]
        );

        // Obtener todos los permisos activos
        $allPermissions = Permission::query()
            ->where('activo', true)
            ->pluck('id');

        if ($allPermissions->isEmpty()) {
            $this->command->warn('⚠ No se encontraron permisos activos. Ejecuta primero: php artisan db:seed --class=PermissionSeeder');
            return;
        }

        // Asignar todos los permisos al cargo
        $cargo->permissions()->sync($allPermissions);

        $this->command->info("✓ Cargo 'super user' creado/actualizado exitosamente.");
        $this->command->info("✓ Total de permisos asignados: {$allPermissions->count()}");

        // Mostrar los permisos asignados agrupados
        $permissions = Permission::query()
            ->whereIn('id', $allPermissions)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        $grouped = $permissions->groupBy('group');
        foreach ($grouped as $group => $perms) {
            $this->command->line("  • {$group}: {$perms->count()} permisos");
        }
    }
}
