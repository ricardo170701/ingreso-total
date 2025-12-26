<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir permisos para cada sección del menú
        $permissions = [
            ['name' => 'view_dashboard', 'description' => 'Ver Dashboard', 'group' => 'dashboard'],
            ['name' => 'view_users', 'description' => 'Ver Usuarios', 'group' => 'users'],
            ['name' => 'create_users', 'description' => 'Crear Usuarios', 'group' => 'users'],
            ['name' => 'edit_users', 'description' => 'Editar Usuarios', 'group' => 'users'],
            ['name' => 'delete_users', 'description' => 'Eliminar Usuarios', 'group' => 'users'],
            ['name' => 'view_puertas', 'description' => 'Ver Puertas', 'group' => 'puertas'],
            ['name' => 'create_puertas', 'description' => 'Crear Puertas', 'group' => 'puertas'],
            ['name' => 'edit_puertas', 'description' => 'Editar Puertas', 'group' => 'puertas'],
            ['name' => 'delete_puertas', 'description' => 'Eliminar Puertas', 'group' => 'puertas'],
            ['name' => 'view_cargos', 'description' => 'Ver Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'create_cargos', 'description' => 'Crear Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'edit_cargos', 'description' => 'Editar Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'delete_cargos', 'description' => 'Eliminar Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'view_ingreso', 'description' => 'Ver Ingreso/QR', 'group' => 'ingreso'],
            ['name' => 'create_ingreso', 'description' => 'Generar Códigos QR', 'group' => 'ingreso'],
            ['name' => 'create_ingreso_otros', 'description' => 'Generar QR para otros usuarios', 'group' => 'ingreso'],
            ['name' => 'view_mantenimientos', 'description' => 'Ver Mantenimientos', 'group' => 'mantenimientos'],
            ['name' => 'create_mantenimientos', 'description' => 'Crear Mantenimientos', 'group' => 'mantenimientos'],
            ['name' => 'edit_mantenimientos', 'description' => 'Editar Mantenimientos', 'group' => 'mantenimientos'],
            ['name' => 'delete_mantenimientos', 'description' => 'Eliminar Mantenimientos', 'group' => 'mantenimientos'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::query()->updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Asignar todos los permisos al rol super_usuario
        $superUsuarioRole = Role::query()->where('name', 'super_usuario')->first();
        if ($superUsuarioRole) {
            $allPermissions = Permission::query()->where('activo', true)->pluck('id');
            $superUsuarioRole->permissions()->sync($allPermissions);
        }

        // Asignar permisos básicos a otros roles
        $operadorRole = Role::query()->where('name', 'operador')->first();
        if ($operadorRole) {
            $operadorPermissions = Permission::query()
                ->whereIn('name', ['view_dashboard', 'view_ingreso', 'create_ingreso', 'create_ingreso_otros'])
                ->pluck('id');
            $operadorRole->permissions()->sync($operadorPermissions);
        }

        $rrhhRole = Role::query()->where('name', 'rrhh')->first();
        if ($rrhhRole) {
            $rrhhPermissions = Permission::query()
                ->whereIn('name', [
                    'view_dashboard',
                    'view_users',
                    'create_users',
                    'edit_users',
                    'view_puertas',
                    'view_cargos',
                    'view_ingreso',
                    'create_ingreso',
                    'create_ingreso_otros',
                ])
                ->pluck('id');
            $rrhhRole->permissions()->sync($rrhhPermissions);
        }

        $funcionarioRole = Role::query()->where('name', 'funcionario')->first();
        if ($funcionarioRole) {
            // Funcionario solo puede crear su propio QR, no tiene create_ingreso_otros
            $funcionarioPermissions = Permission::query()
                ->whereIn('name', ['view_dashboard', 'view_ingreso', 'create_ingreso'])
                ->pluck('id');
            $funcionarioRole->permissions()->sync($funcionarioPermissions);
        }
    }
}
