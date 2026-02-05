<?php

namespace Database\Seeders;

use App\Models\CodigoQr;
use Illuminate\Database\Seeder;

class CleanCodigosQrDesactivadosSeeder extends Seeder
{
    /**
     * Elimina los códigos QR desactivados (activo = false).
     *
     * - Los registros en accesos que referenciaban esos QR quedarán con codigo_qr_id = null.
     * - Las filas en codigo_qr_puerta_acceso se eliminan en cascada.
     *
     * Para ejecutar:
     *   php artisan db:seed --class=CleanCodigosQrDesactivadosSeeder
     */
    public function run(): void
    {
        $count = CodigoQr::query()->where('activo', false)->count();

        if ($count === 0) {
            $this->command->info('No hay códigos QR desactivados. Nada que eliminar.');
            return;
        }

        $this->command->info("Se eliminarán {$count} código(s) QR desactivado(s).");

        if (app()->environment('production') && !$this->command->confirm('¿Continuar en producción?', false)) {
            $this->command->warn('Operación cancelada.');
            return;
        }

        $deleted = CodigoQr::query()->where('activo', false)->delete();
        $this->command->info("✓ Eliminados {$deleted} código(s) QR desactivado(s).");
    }
}
