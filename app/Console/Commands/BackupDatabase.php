<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
        {--retention-days= : Días de retención (por defecto DB_BACKUP_RETENTION_DAYS o 30)}
        {--dir= : Directorio de backups (por defecto DB_BACKUP_DIR o base_path("backups"))}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un backup comprimido (mysqldump + gzip) y limpia backups antiguos.';

    public function handle(): int
    {
        $connection = (string) env('DB_CONNECTION', 'mysql');
        if ($connection !== 'mysql') {
            $this->error("DB_CONNECTION='{$connection}' no soportado por este comando (solo mysql).");
            return self::FAILURE;
        }

        $dbHost = (string) env('DB_HOST', 'db');
        $dbPort = (string) env('DB_PORT', '3306');
        $dbName = (string) env('DB_DATABASE', '');
        $dbUser = (string) env('DB_USERNAME', '');
        $dbPassword = (string) env('DB_PASSWORD', '');

        if ($dbName === '' || $dbUser === '') {
            $this->error('Faltan variables DB_DATABASE/DB_USERNAME.');
            return self::FAILURE;
        }

        $backupDir = (string) ($this->option('dir') ?: env('DB_BACKUP_DIR', base_path('backups')));
        $retentionDays = (int) ($this->option('retention-days') ?: env('DB_BACKUP_RETENTION_DAYS', 30));
        if ($retentionDays < 1) {
            $retentionDays = 30;
        }

        File::ensureDirectoryExists($backupDir);

        $timestamp = Carbon::now()->format('Ymd_His');
        $backupFile = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "backup_{$dbName}_{$timestamp}.sql.gz";

        $this->info('==========================================');
        $this->info('Backup de Base de Datos (automático)');
        $this->info('==========================================');
        $this->line("DB: {$dbName} @ {$dbHost}:{$dbPort}");
        $this->line("Archivo: {$backupFile}");

        // mysqldump -> gzip
        // - Usamos MYSQL_PWD para no exponer contraseña en argv (mejor práctica)
        // - --single-transaction: consistente (InnoDB)
        // - --routines/--triggers: incluye objetos
        // - --column-statistics=0: evita warnings/errores en algunas versiones
        $cmd = [
            'sh',
            '-lc',
            implode(' ', array_map('escapeshellarg', [
                'mysqldump',
                "-h{$dbHost}",
                "-P{$dbPort}",
                "-u{$dbUser}",
                '--single-transaction',
                '--routines',
                '--triggers',
                '--column-statistics=0',
                $dbName,
            ])) . ' | gzip > ' . escapeshellarg($backupFile),
        ];

        $process = new Process($cmd, base_path(), [
            'MYSQL_PWD' => $dbPassword,
        ]);
        $process->setTimeout((int) env('DB_BACKUP_TIMEOUT_SECONDS', 600)); // 10 min por defecto

        try {
            $process->mustRun(function ($type, $buffer) {
                // Mostrar salida útil (sin saturar)
                $chunk = trim((string) $buffer);
                if ($chunk !== '') {
                    $this->line($chunk);
                }
            });
        } catch (ProcessFailedException $e) {
            $this->error('ERROR: Falló el backup (mysqldump/gzip).');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if (!File::exists($backupFile) || File::size($backupFile) <= 0) {
            $this->error('ERROR: El archivo de backup no fue creado o está vacío.');
            return self::FAILURE;
        }

        $sizeMb = round(File::size($backupFile) / 1024 / 1024, 2);
        $this->info("✓ Backup completado: {$backupFile} ({$sizeMb} MB)");

        // Limpieza por retención
        $this->line('');
        $this->info("Limpiando backups antiguos (>{$retentionDays} días) ...");

        $deleted = 0;
        $cutoff = Carbon::now()->subDays($retentionDays)->getTimestamp();

        foreach (File::glob(rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'backup_*.sql.gz') as $file) {
            $mtime = @filemtime($file) ?: 0;
            if ($mtime > 0 && $mtime < $cutoff) {
                try {
                    File::delete($file);
                    $deleted++;
                } catch (\Throwable) {
                    // continuar
                }
            }
        }

        $this->info("✓ Limpieza completada. Eliminados: {$deleted}");

        $this->line('');
        $this->info('==========================================');
        $this->info('Backup completado exitosamente');
        $this->info('==========================================');

        return self::SUCCESS;
    }
}

