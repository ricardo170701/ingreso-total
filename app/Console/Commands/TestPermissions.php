<?php

namespace App\Console\Commands;

use App\Models\Cargo;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;

class TestPermissions extends Command
{
    protected $signature = 'test:permissions {--user-id=1}';
    protected $description = 'Verificar que el sistema de permisos funciona correctamente';

    public function handle()
    {
        $this->info('🔍 Verificando sistema de permisos...');
        $this->newLine();

        // 1. Verificar que existen permisos
        $totalPermissions = Permission::where('activo', true)->count();
        $this->info("✓ Total de permisos activos: {$totalPermissions}");

        if ($totalPermissions < 28) {
            $this->warn("⚠️  Se esperaban 28 permisos, se encontraron {$totalPermissions}");
            $this->info("Ejecuta: php artisan db:seed --class=PermissionSeeder");
        }

        // 2. Verificar permisos por grupo
        $groups = Permission::where('activo', true)
            ->select('group')
            ->distinct()
            ->pluck('group');

        $this->info("✓ Grupos de permisos: " . $groups->count());
        foreach ($groups as $group) {
            $count = Permission::where('group', $group)->where('activo', true)->count();
            $this->line("  • {$group}: {$count} permisos");
        }

        // 3. Verificar usuario de prueba
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->newLine();
        $this->info("👤 Probando con usuario: {$user->name} ({$user->email})");
        $this->line("   Cargo: " . ($user->cargo ? $user->cargo->name : 'Sin cargo'));
        $this->line("   Rol: " . ($user->role ? $user->role->name : 'Sin rol'));

        // 4. Verificar que tiene permisos cargados
        $userPermissions = $user->permissions;
        $this->newLine();
        $this->info("📋 Permisos del usuario: " . count($userPermissions));

        if (count($userPermissions) === 0) {
            $this->warn("⚠️  El usuario no tiene permisos asignados");
            $this->info("   Asigna permisos al cargo del usuario desde la sección de Cargos");
        } else {
            $this->line("   Primeros 5 permisos: " . implode(', ', array_slice($userPermissions, 0, 5)));
        }

        // 5. Probar algunos permisos clave
        $this->newLine();
        $this->info("🔐 Probando permisos específicos:");

        $testPermissions = [
            'view_dashboard',
            'view_users',
            'view_puertas',
            'view_mantenimientos',
            'view_cargos',
            'view_departamentos',
            'view_reportes',
            'view_soporte',
            'view_protocolo',
        ];

        foreach ($testPermissions as $perm) {
            $has = $user->hasPermission($perm);
            $icon = $has ? '✓' : '✗';
            $this->line("   {$icon} {$perm}: " . ($has ? 'SÍ' : 'NO'));
        }

        // 6. Verificar Policies registradas
        $this->newLine();
        $this->info("🛡️  Verificando Policies:");

        $policies = [
            'User' => 'UserPolicy',
            'Puerta' => 'PuertaPolicy',
            'Mantenimiento' => 'MantenimientoPolicy',
            'Cargo' => 'CargoPolicy',
            'ProtocolRun' => 'ProtocolRunPolicy',
            'Departamento' => 'DepartamentoPolicy',
        ];

        foreach ($policies as $model => $policy) {
            $policyClass = "App\\Policies\\{$policy}";
            if (class_exists($policyClass)) {
                $this->line("   ✓ {$model} → {$policy}");
            } else {
                $this->error("   ✗ {$model} → {$policy} (NO EXISTE)");
            }
        }

        // 7. Verificar que el cargo tiene relación con permisos
        if ($user->cargo) {
            $this->newLine();
            $cargoPermissions = $user->cargo->permissions()->where('activo', true)->count();
            $this->info("📦 Permisos del cargo '{$user->cargo->name}': {$cargoPermissions}");
        }

        $this->newLine();
        $this->info("✅ Verificación completada!");

        return 0;
    }
}

