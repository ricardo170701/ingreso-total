<?php

namespace App\Console\Commands;

use App\Models\MantenimientoDocumento;
use App\Models\MantenimientoImagen;
use App\Models\Puerta;
use App\Models\Ups;
use App\Models\UpsMantenimientoDocumento;
use App\Models\UpsMantenimientoImagen;
use App\Models\User;
use App\Models\UserDocumento;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CleanupStorageOrphans extends Command
{
    /**
     * Uso:
     *  - Vista previa (no borra): php artisan storage:cleanup-orphans
     *  - Borrar huérfanos:        php artisan storage:cleanup-orphans --delete
     */
    protected $signature = 'storage:cleanup-orphans
        {--disk=all : public|local|all}
        {--delete : Eliminar archivos huérfanos (si no, solo muestra)}
        {--limit=200 : Máximo de archivos listados en consola}
        {--tmp-hours=24 : Para storage/app/tmp: solo considerar huérfanos si son más viejos que estas horas}
        {--no-tmp : No revisar storage/app/tmp}';

    protected $description = 'Limpia archivos huérfanos en storage comparando disco vs referencias en BD (public y local).';

    public function handle(): int
    {
        $diskOpt = strtolower((string) $this->option('disk'));
        $doDelete = (bool) $this->option('delete');
        $limit = max(0, (int) $this->option('limit'));
        $tmpHours = max(0, (int) $this->option('tmp-hours'));
        $checkTmp = !(bool) $this->option('no-tmp');

        if (!in_array($diskOpt, ['public', 'local', 'all'], true)) {
            $this->error('Opción --disk inválida. Usa: public|local|all');
            return self::INVALID;
        }

        $this->info('Recolectando rutas referenciadas en BD...');

        /** @var array<string, bool> $publicReferenced */
        $publicReferenced = $this->buildPublicReferencedSet();

        /** @var array<string, bool> $localReferenced */
        $localReferenced = $this->buildLocalReferencedSet();

        $totalDeleted = 0;
        $totalCandidates = 0;

        if ($diskOpt === 'public' || $diskOpt === 'all') {
            [$candidates, $deleted] = $this->scanAndMaybeDeletePublic($publicReferenced, $doDelete, $limit);
            $totalCandidates += $candidates;
            $totalDeleted += $deleted;
        }

        if ($diskOpt === 'local' || $diskOpt === 'all') {
            [$candidates, $deleted] = $this->scanAndMaybeDeleteLocal($localReferenced, $doDelete, $limit, $tmpHours, $checkTmp);
            $totalCandidates += $candidates;
            $totalDeleted += $deleted;
        }

        $this->newLine();
        $this->info("Listo. Huérfanos detectados: {$totalCandidates}. Eliminados: {$totalDeleted}.");
        if (!$doDelete) {
            $this->comment('Modo vista previa: ejecuta con --delete para eliminar.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, bool>
     */
    private function buildPublicReferencedSet(): array
    {
        $paths = collect()
            // Puertas (imagen)
            ->merge(Puerta::query()->whereNotNull('imagen')->pluck('imagen'))
            // UPS (foto)
            ->merge(Ups::query()->whereNotNull('foto')->pluck('foto'))
            // Usuarios (foto perfil)
            ->merge(User::query()->whereNotNull('foto_perfil')->pluck('foto_perfil'))
            // Mantenimientos puertas (documentos + imágenes)
            ->merge(MantenimientoDocumento::query()->whereNotNull('ruta_documento')->pluck('ruta_documento'))
            ->merge(MantenimientoImagen::query()->whereNotNull('ruta_imagen')->pluck('ruta_imagen'))
            // Mantenimientos UPS (documentos + imágenes)
            ->merge(UpsMantenimientoDocumento::query()->whereNotNull('ruta_documento')->pluck('ruta_documento'))
            ->merge(UpsMantenimientoImagen::query()->whereNotNull('ruta_imagen')->pluck('ruta_imagen'));

        return $this->toPathSet($paths);
    }

    /**
     * @return array<string, bool>
     */
    private function buildLocalReferencedSet(): array
    {
        // UserDocumento::path se guarda sin disco (store(...) -> disk local por defecto)
        $paths = UserDocumento::query()->whereNotNull('path')->pluck('path');
        return $this->toPathSet($paths);
    }

    /**
     * @param Collection<int, mixed> $paths
     * @return array<string, bool>
     */
    private function toPathSet(Collection $paths): array
    {
        $set = [];
        foreach ($paths as $p) {
            $path = trim((string) $p);
            if ($path === '') {
                continue;
            }
            // Evitar valores inesperados
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                continue;
            }
            // Normalizar separadores (Windows)
            $path = str_replace('\\', '/', $path);
            // Evitar rutas absolutas por seguridad
            $path = ltrim($path, '/');
            $set[$path] = true;
        }
        return $set;
    }

    /**
     * @param array<string,bool> $referenced
     * @return array{0:int,1:int} [candidates, deleted]
     */
    private function scanAndMaybeDeletePublic(array $referenced, bool $doDelete, int $limit): array
    {
        $this->newLine();
        $this->info('Revisando disk public (storage/app/public)...');

        $disk = Storage::disk('public');
        $allFiles = $disk->allFiles();

        $orphans = [];
        $totalSize = 0;

        foreach ($allFiles as $file) {
            $file = str_replace('\\', '/', (string) $file);
            // No tocar dotfiles
            if (str_starts_with(basename($file), '.')) {
                continue;
            }
            if (!isset($referenced[$file])) {
                $orphans[] = $file;
                try {
                    $totalSize += (int) $disk->size($file);
                } catch (\Throwable) {
                    // ignore
                }
            }
        }

        $this->line('Huérfanos (public): ' . count($orphans) . ' · Tamaño aprox: ' . $this->formatBytes($totalSize));

        $deleted = 0;
        if (count($orphans) > 0) {
            $this->renderSample('public', $orphans, $limit);
            if ($doDelete) {
                foreach ($orphans as $file) {
                    if ($disk->exists($file) && $disk->delete($file)) {
                        $deleted++;
                    }
                }
                $this->line("Eliminados (public): {$deleted}");
            }
        }

        return [count($orphans), $deleted];
    }

    /**
     * @param array<string,bool> $referenced
     * @return array{0:int,1:int} [candidates, deleted]
     */
    private function scanAndMaybeDeleteLocal(array $referenced, bool $doDelete, int $limit, int $tmpHours, bool $checkTmp): array
    {
        $this->newLine();
        $this->info('Revisando disk local (storage/app)...');

        $disk = Storage::disk('local');

        $candidates = 0;
        $deleted = 0;

        // 1) user_documentos/* (huérfanos por BD)
        if ($disk->exists('user_documentos')) {
            $files = $disk->allFiles('user_documentos');
            $orphans = [];
            $totalSize = 0;

            foreach ($files as $file) {
                $file = str_replace('\\', '/', (string) $file);
                if (str_starts_with(basename($file), '.')) {
                    continue;
                }
                if (!isset($referenced[$file])) {
                    $orphans[] = $file;
                    try {
                        $totalSize += (int) $disk->size($file);
                    } catch (\Throwable) {
                        // ignore
                    }
                }
            }

            $this->line('Huérfanos (local:user_documentos): ' . count($orphans) . ' · Tamaño aprox: ' . $this->formatBytes($totalSize));
            $this->renderSample('local:user_documentos', $orphans, $limit);

            $candidates += count($orphans);
            if ($doDelete) {
                foreach ($orphans as $file) {
                    if ($disk->exists($file) && $disk->delete($file)) {
                        $deleted++;
                    }
                }
                if (count($orphans) > 0) {
                    $this->line("Eliminados (local:user_documentos): {$deleted}");
                }
            }
        } else {
            $this->line('Carpeta local:user_documentos no existe (ok).');
        }

        // 2) tmp/* (archivos temporales viejos)
        if ($checkTmp) {
            $tmpDir = 'tmp';
            if ($disk->exists($tmpDir)) {
                $files = $disk->allFiles($tmpDir);
                $oldFiles = [];
                $totalSize = 0;
                $cutoff = now()->subHours($tmpHours)->timestamp;

                foreach ($files as $file) {
                    $file = str_replace('\\', '/', (string) $file);
                    if (str_starts_with(basename($file), '.')) {
                        continue;
                    }
                    try {
                        $lm = (int) $disk->lastModified($file);
                    } catch (\Throwable) {
                        $lm = 0;
                    }
                    if ($lm > 0 && $lm < $cutoff) {
                        $oldFiles[] = $file;
                        try {
                            $totalSize += (int) $disk->size($file);
                        } catch (\Throwable) {
                            // ignore
                        }
                    }
                }

                $this->line("Candidatos (local:tmp) > {$tmpHours}h: " . count($oldFiles) . ' · Tamaño aprox: ' . $this->formatBytes($totalSize));
                $this->renderSample('local:tmp', $oldFiles, $limit);

                $candidates += count($oldFiles);
                if ($doDelete) {
                    $delTmp = 0;
                    foreach ($oldFiles as $file) {
                        if ($disk->exists($file) && $disk->delete($file)) {
                            $delTmp++;
                        }
                    }
                    $deleted += $delTmp;
                    if (count($oldFiles) > 0) {
                        $this->line("Eliminados (local:tmp): {$delTmp}");
                    }
                }
            } else {
                $this->line('Carpeta local:tmp no existe (ok).');
            }
        } else {
            $this->line('Skip local:tmp por --no-tmp.');
        }

        return [$candidates, $deleted];
    }

    /**
     * @param array<int,string> $files
     */
    private function renderSample(string $label, array $files, int $limit): void
    {
        if (count($files) === 0) {
            return;
        }

        $take = $limit <= 0 ? count($files) : min($limit, count($files));
        $sample = array_slice($files, 0, $take);

        $rows = [];
        foreach ($sample as $f) {
            $rows[] = [$f];
        }

        $this->newLine();
        $this->comment("Muestra ({$label}) - mostrando {$take} de " . count($files));
        $this->table(['Archivo'], $rows);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        $kb = $bytes / 1024;
        if ($kb < 1024) return number_format($kb, 2) . ' KB';
        $mb = $kb / 1024;
        if ($mb < 1024) return number_format($mb, 2) . ' MB';
        $gb = $mb / 1024;
        return number_format($gb, 2) . ' GB';
    }
}
