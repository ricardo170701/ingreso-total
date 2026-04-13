<?php

namespace App\Console\Commands;

use App\Models\Ups;
use App\Models\UpsBitacoraImagen;
use App\Models\UpsMantenimientoDocumento;
use App\Models\UpsMantenimientoImagen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpsPurgeHistoricalMediaCommand extends Command
{
    protected $signature = 'ups:purge-historical-media
                            {--dry-run : Mostrar conteos sin borrar}
                            {--with-ups-assets : También quitar foto y ficha técnica de la tabla UPS}';

    protected $description = 'Purga en disco y BD los adjuntos históricos de UPS (imágenes de bitácora, fotos/documentos de mantenimientos; opcional foto/ficha de la UPS).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $withUpsAssets = (bool) $this->option('with-ups-assets');

        $this->info($dry ? 'Modo dry-run (no se elimina nada).' : 'Eliminando archivos y registros…');

        $bitacoraImgs = UpsBitacoraImagen::query()->get();
        $mantImgs = UpsMantenimientoImagen::query()->get();
        $mantDocs = UpsMantenimientoDocumento::query()->get();

        $this->line('Imágenes bitácora (filas): ' . $bitacoraImgs->count());
        $this->line('Imágenes mantenimiento UPS (filas): ' . $mantImgs->count());
        $this->line('Documentos mantenimiento UPS (filas): ' . $mantDocs->count());

        if ($dry) {
            if ($withUpsAssets) {
                $n = Ups::query()->where(function ($q) {
                    $q->whereNotNull('foto')->orWhereNotNull('ficha_tecnica');
                })->count();
                $this->line("UPS con foto o ficha técnica: {$n}");
            }

            return self::SUCCESS;
        }

        DB::transaction(function () use ($bitacoraImgs, $mantImgs, $mantDocs, $withUpsAssets) {
            foreach ($bitacoraImgs as $row) {
                if ($row->ruta_imagen && Storage::disk('public')->exists($row->ruta_imagen)) {
                    Storage::disk('public')->delete($row->ruta_imagen);
                }
                $row->delete();
            }
            foreach ($mantImgs as $row) {
                if ($row->ruta_imagen && Storage::disk('public')->exists($row->ruta_imagen)) {
                    Storage::disk('public')->delete($row->ruta_imagen);
                }
                $row->delete();
            }
            foreach ($mantDocs as $row) {
                if ($row->ruta_documento && Storage::disk('public')->exists($row->ruta_documento)) {
                    Storage::disk('public')->delete($row->ruta_documento);
                }
                $row->delete();
            }

            if ($withUpsAssets) {
                $upsList = Ups::query()->get();
                foreach ($upsList as $u) {
                    if ($u->foto && Storage::disk('public')->exists($u->foto)) {
                        Storage::disk('public')->delete($u->foto);
                    }
                    if ($u->ficha_tecnica && Storage::disk('public')->exists($u->ficha_tecnica)) {
                        Storage::disk('public')->delete($u->ficha_tecnica);
                    }
                    $u->forceFill(['foto' => null, 'ficha_tecnica' => null])->saveQuietly();
                }
            }
        });

        foreach (['ups/bitacora', 'ups/bitacora/temp', 'ups/mantenimientos/fotos', 'ups/mantenimientos/documentos'] as $dir) {
            if (Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->deleteDirectory($dir);
                $this->line("Directorio eliminado: {$dir}");
            }
        }

        $this->info('Purga UPS completada.');

        return self::SUCCESS;
    }
}
