<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

class ListPermissions extends Command
{
    protected $signature = 'permissions:list';
    protected $description = 'Listar todos los permisos del sistema agrupados por categoría';

    public function handle()
    {
        $permissions = Permission::query()
            ->where('activo', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        if ($permissions->isEmpty()) {
            $this->error('No hay permisos registrados en el sistema.');
            return 1;
        }

        $this->info("Total de permisos: {$permissions->count()}");
        $this->newLine();

        $grouped = $permissions->groupBy('group');

        foreach ($grouped as $group => $perms) {
            $this->line("<fg=cyan;options=bold>Grupo: {$group}</>");

            foreach ($perms as $perm) {
                $this->line("  • <fg=green>{$perm->name}</> - {$perm->description}");
            }

            $this->newLine();
        }

        return 0;
    }
}
