<?php

namespace Database\Seeders;

use App\Models\Permission;
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
            // Dashboard
            ['name' => 'view_dashboard', 'description' => 'Ver Dashboard', 'group' => 'dashboard'],

            // Usuarios
            ['name' => 'view_users', 'description' => 'Ver Usuarios', 'group' => 'users'],
            ['name' => 'create_users', 'description' => 'Crear Usuarios', 'group' => 'users'],
            ['name' => 'edit_users', 'description' => 'Editar Usuarios', 'group' => 'users'],
            ['name' => 'edit_user_role', 'description' => 'Editar rol de usuario', 'group' => 'users'],
            ['name' => 'delete_users', 'description' => 'Eliminar Usuarios', 'group' => 'users'],

            // Puertas
            ['name' => 'view_puertas', 'description' => 'Ver Puertas', 'group' => 'puertas'],
            ['name' => 'create_puertas', 'description' => 'Crear Puertas', 'group' => 'puertas'],
            ['name' => 'edit_puertas', 'description' => 'Editar Puertas', 'group' => 'puertas'],
            ['name' => 'delete_puertas', 'description' => 'Eliminar Puertas', 'group' => 'puertas'],
            ['name' => 'toggle_puertas', 'description' => 'Abrir/Cerrar Puertas Manualmente', 'group' => 'puertas'],
            ['name' => 'reboot_puertas', 'description' => 'Reiniciar Raspberry Pi de Puertas', 'group' => 'puertas'],
            ['name' => 'view_puertas_ocultas', 'description' => 'Ver Puertas Ocultas', 'group' => 'puertas'],
            ['name' => 'edit_puerta_codigo_fisico', 'description' => 'Editar código físico (entrada/salida) de puertas ya creadas', 'group' => 'puertas'],

            // Zonas
            ['name' => 'view_zonas', 'description' => 'Ver Zonas', 'group' => 'zonas'],
            ['name' => 'create_zonas', 'description' => 'Crear Zonas', 'group' => 'zonas'],
            ['name' => 'edit_zonas', 'description' => 'Editar Zonas', 'group' => 'zonas'],
            ['name' => 'delete_zonas', 'description' => 'Eliminar Zonas', 'group' => 'zonas'],

            // Cargos/Permisos
            ['name' => 'view_cargos', 'description' => 'Ver Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'create_cargos', 'description' => 'Crear Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'edit_cargos', 'description' => 'Editar Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'delete_cargos', 'description' => 'Eliminar Permisos/Cargos', 'group' => 'cargos'],
            ['name' => 'view_cargos_permiso_superior', 'description' => 'Ver y asignar cargos con permiso superior', 'group' => 'cargos'],

            // Roles (pantalla "Permisos del Sistema")
            ['name' => 'view_roles', 'description' => 'Ver Roles / Permisos del Sistema', 'group' => 'roles'],
            ['name' => 'edit_roles', 'description' => 'Editar Roles / Permisos del Sistema', 'group' => 'roles'],

            // Departamentos
            ['name' => 'view_departamentos', 'description' => 'Ver Departamentos', 'group' => 'departamentos'],
            ['name' => 'create_departamentos', 'description' => 'Crear Departamentos', 'group' => 'departamentos'],
            ['name' => 'edit_departamentos', 'description' => 'Editar Departamentos', 'group' => 'departamentos'],
            ['name' => 'delete_departamentos', 'description' => 'Eliminar Departamentos', 'group' => 'departamentos'],

            // Ingreso/QR
            ['name' => 'view_ingreso', 'description' => 'Ver Ingreso/QR', 'group' => 'ingreso'],
            ['name' => 'create_ingreso', 'description' => 'Generar Códigos QR', 'group' => 'ingreso'],
            ['name' => 'create_ingreso_otros', 'description' => 'Generar QR para otros usuarios', 'group' => 'ingreso'],
            ['name' => 'view_ingreso_funcionarios', 'description' => 'Ver Funcionarios en selector de Ingreso', 'group' => 'ingreso'],
            ['name' => 'create_ingreso_visitantes', 'description' => 'Crear usuarios visitantes desde Ingreso', 'group' => 'ingreso'],

            // Tarjetas NFC
            ['name' => 'view_tarjetas_nfc', 'description' => 'Ver Tarjetas NFC', 'group' => 'tarjetas_nfc'],
            ['name' => 'create_tarjetas_nfc', 'description' => 'Crear Tarjetas NFC', 'group' => 'tarjetas_nfc'],
            ['name' => 'edit_tarjetas_nfc', 'description' => 'Editar Tarjetas NFC', 'group' => 'tarjetas_nfc'],
            ['name' => 'delete_tarjetas_nfc', 'description' => 'Eliminar Tarjetas NFC', 'group' => 'tarjetas_nfc'],
            ['name' => 'asignar_tarjetas_nfc', 'description' => 'Asignar Tarjetas NFC desde Ingreso', 'group' => 'ingreso'],

            // Mantenimientos
            ['name' => 'view_mantenimientos', 'description' => 'Ver Mantenimientos', 'group' => 'mantenimientos'],
            ['name' => 'create_mantenimientos', 'description' => 'Crear Mantenimientos', 'group' => 'mantenimientos'],
            ['name' => 'edit_mantenimientos', 'description' => 'Editar Mantenimientos', 'group' => 'mantenimientos'],
            ['name' => 'delete_mantenimientos', 'description' => 'Eliminar Mantenimientos', 'group' => 'mantenimientos'],

            // UPS
            ['name' => 'view_ups', 'description' => 'Ver UPS', 'group' => 'ups'],
            ['name' => 'create_ups', 'description' => 'Crear UPS', 'group' => 'ups'],
            ['name' => 'edit_ups', 'description' => 'Editar UPS', 'group' => 'ups'],
            ['name' => 'delete_ups', 'description' => 'Eliminar UPS', 'group' => 'ups'],
            ['name' => 'create_ups_mantenimientos', 'description' => 'Crear Mantenimientos de UPS', 'group' => 'ups'],
            ['name' => 'edit_ups_mantenimientos', 'description' => 'Editar Mantenimientos de UPS', 'group' => 'ups'],
            ['name' => 'delete_ups_mantenimientos', 'description' => 'Eliminar Mantenimientos de UPS', 'group' => 'ups'],

            // Reportes
            ['name' => 'view_reportes', 'description' => 'Ver y Exportar Reportes', 'group' => 'reportes'],

            // Soporte
            ['name' => 'view_soporte', 'description' => 'Ver Soporte/FAQs', 'group' => 'soporte'],

            // Protocolo de Emergencia
            ['name' => 'view_protocolo', 'description' => 'Ver Protocolo de Emergencia', 'group' => 'protocolo'],
            ['name' => 'protocol_emergencia_open_all', 'description' => 'Ejecutar Protocolo de Emergencia (Abrir todas las puertas)', 'group' => 'protocolo'],

            // Datacenter
            ['name' => 'access_datacenter', 'description' => 'Acceso a puertas de datacenter', 'group' => 'puertas'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::query()->updateOrCreate(
                ['name' => $permissionData['name']],
                array_merge($permissionData, ['activo' => true])
            );
        }

        $this->command->info('✓ Permisos del sistema actualizados: ' . count($permissions));
    }
}
