<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimitRolesToFlags extends Command
{
    protected $signature = 'roles:limit-to-flags {--dry-run : No aplica cambios, solo muestra qué haría}';

    protected $description = 'Limita los roles a funcionario/visitante, reasigna usuarios con roles antiguos y elimina roles no permitidos.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $allowed = ['funcionario', 'visitante'];

        $this->info('Roles permitidos: ' . implode(', ', $allowed));
        if ($dryRun) {
            $this->warn('DRY RUN: no se aplicarán cambios.');
        }

        return DB::transaction(function () use ($dryRun, $allowed) {
            // 1) Asegurar roles permitidos
            foreach ($allowed as $name) {
                Role::query()->updateOrCreate(
                    ['name' => $name],
                    ['description' => $name === 'visitante'
                        ? 'Usuario externo (QR por correo / accesos embebidos)'
                        : 'Usuario interno (permisos por cargo)']
                );
            }

            $roleFuncionarioId = Role::query()->where('name', 'funcionario')->value('id');
            if (!$roleFuncionarioId) {
                $this->error('No se pudo resolver el rol funcionario.');
                return 1;
            }

            // 2) Reasignar usuarios con rol no permitido -> funcionario
            $usersToMove = User::query()
                ->whereNotNull('role_id')
                ->whereHas('role', fn($q) => $q->whereNotIn('name', $allowed))
                ->count();

            $this->line("Usuarios con rol no permitido: {$usersToMove}");

            if (!$dryRun && $usersToMove > 0) {
                User::query()
                    ->whereNotNull('role_id')
                    ->whereHas('role', fn($q) => $q->whereNotIn('name', $allowed))
                    ->update(['role_id' => $roleFuncionarioId]);
            }

            // 3) Eliminar roles no permitidos (y su pivot role_permission)
            $rolesToDelete = Role::query()
                ->whereNotIn('name', $allowed)
                ->pluck('id');

            $this->line('Roles a eliminar: ' . $rolesToDelete->count());

            if (!$dryRun && $rolesToDelete->isNotEmpty()) {
                DB::table('role_permission')->whereIn('role_id', $rolesToDelete)->delete();
                Role::query()->whereIn('id', $rolesToDelete)->delete();
            }

            $this->info('✓ Roles limitados correctamente.');
            return 0;
        });
    }
}
