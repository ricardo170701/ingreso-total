<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Inactivar automáticamente usuarios expirados (diario)
        $schedule->command('users:deactivate-expired')->dailyAt('00:10');

        // Backup automático de base de datos (diario)
        // Requiere cron corriendo dentro del contenedor (ver Dockerfile2 + docker/production-entrypoint.sh)
        $schedule->command('db:backup')
            ->dailyAt(env('DB_BACKUP_TIME', '02:00'))
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/db-backup.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
