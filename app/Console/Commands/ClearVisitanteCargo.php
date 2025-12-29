<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class ClearVisitanteCargo extends Command
{
    protected $signature = 'users:clear-visitante-cargo {--dry-run : Solo muestra cuántos usuarios se actualizarían}';

    protected $description = 'Elimina cargo_id de usuarios con rol visitante (cargo_id => null).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $visitanteRoleId = Role::query()->where('name', 'visitante')->value('id');
        if (!$visitanteRoleId) {
            $this->error('No se encontró el rol visitante.');
            return 1;
        }

        $q = User::query()
            ->where('role_id', $visitanteRoleId)
            ->whereNotNull('cargo_id');

        $count = $q->count();

        if ($dryRun) {
            $this->info("DRY RUN: visitantes con cargo_id asignado: {$count}");
            return 0;
        }

        $q->update(['cargo_id' => null]);
        $this->info("✓ Visitantes actualizados (cargo_id = null): {$count}");

        return 0;
    }
}
