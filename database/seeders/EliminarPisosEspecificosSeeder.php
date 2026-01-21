<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EliminarPisosEspecificosSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para eliminar pisos específicos.
     * 
     * Este seeder elimina directamente los siguientes pisos (son errores, no tienen relación):
     * - SOTANO
     * - ALMACEN
     * - SECRETARIA DE SALUD
     * - MIGRACION
     * 
     * Para ejecutar:
     * php artisan db:seed --class=EliminarPisosEspecificosSeeder
     */
    public function run(): void
    {
        $nombresPisosAEliminar = [
            'SOTANO',
            'ALMACEN',
            'SECRETARIA DE SALUD',
            'MIGRACION',
        ];

        $this->command->info('🗑️  Eliminando pisos específicos...');

        // Eliminar directamente usando SQL (búsqueda insensible a mayúsculas/minúsculas)
        $eliminados = DB::table('pisos')
            ->whereRaw('UPPER(nombre) IN (?)', [array_map('strtoupper', $nombresPisosAEliminar)])
            ->delete();

        $this->command->info("✓ Pisos eliminados: {$eliminados}");
        $this->command->info('✅ Proceso completado!');
    }
}
