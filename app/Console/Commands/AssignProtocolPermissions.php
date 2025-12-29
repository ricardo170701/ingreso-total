<?php

namespace App\Console\Commands;

use App\Models\Cargo;
use App\Models\Permission;
use Illuminate\Console\Command;

class AssignProtocolPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'protocol:assign-permissions {--cargo=Super Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna los permisos del protocolo de emergencia al cargo especificado (por defecto: Super Admin)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cargoName = $this->option('cargo');

        $cargo = Cargo::query()->where('name', $cargoName)->first();

        if (!$cargo) {
            $this->error("Cargo '{$cargoName}' no encontrado.");
            return 1;
        }

        $permissions = Permission::query()
            ->whereIn('name', ['view_protocolo', 'protocol_emergencia_open_all'])
            ->where('activo', true)
            ->pluck('id');

        if ($permissions->isEmpty()) {
            $this->error('Los permisos del protocolo no se encontraron. Ejecuta primero: php artisan db:seed --class=PermissionSeeder');
            return 1;
        }

        // Sincronizar permisos (agregar sin quitar los existentes)
        $existingPermissions = $cargo->permissions()->pluck('permissions.id');
        $allPermissions = $existingPermissions->merge($permissions)->unique();

        $cargo->permissions()->sync($allPermissions);

        $this->info("✓ Permisos del protocolo asignados al cargo '{$cargoName}':");
        $this->line("  - view_protocolo");
        $this->line("  - protocol_emergencia_open_all");
        $this->info("\nTotal de permisos del cargo: {$allPermissions->count()}");

        return 0;
    }
}
