<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeactivateExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:deactivate-expired {--dry-run : No actualiza, solo muestra el conteo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca como inactivos a los usuarios cuya fecha_expiracion ya pasó';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoy = Carbon::now()->startOfDay();

        $q = User::query()
            ->where('activo', true)
            ->whereNotNull('fecha_expiracion')
            ->whereDate('fecha_expiracion', '<', $hoy->toDateString());

        $count = (clone $q)->count();

        if ($this->option('dry-run')) {
            $this->info("Usuarios a inactivar (dry-run): {$count}");
            return self::SUCCESS;
        }

        $updated = $q->update([
            'activo' => false,
            'updated_at' => now(),
        ]);

        $this->info("Usuarios inactivados: {$updated}");
        return self::SUCCESS;
    }
}

