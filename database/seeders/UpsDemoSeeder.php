<?php

namespace Database\Seeders;

use App\Models\Piso;
use App\Models\Ups;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener un piso si existe (opcional)
        $piso = Piso::first();

        $ups = [
            [
                'codigo' => 'UPS-DEMO-001',
                'nombre' => 'UPS Principal - Cuarto Eléctrico',
                'piso_id' => $piso?->id,
                'ubicacion' => 'Cuarto Eléctrico Principal',
                'marca' => 'APC',
                'modelo' => 'Smart-UPS RT 5000',
                'serial' => 'AS123456789',
                'potencia_va' => 5000,
                'potencia_w' => 4500,
                'activo' => true,
                'observaciones' => 'UPS de demostración para pruebas del sistema',
            ],
            [
                'codigo' => 'UPS-DEMO-002',
                'nombre' => 'UPS Respaldo - Rack Servidores',
                'piso_id' => $piso?->id,
                'ubicacion' => 'Rack de Servidores - Sala TI',
                'marca' => 'CyberPower',
                'modelo' => 'PR1500LCDRT2U',
                'serial' => 'CP987654321',
                'potencia_va' => 1500,
                'potencia_w' => 1350,
                'activo' => true,
                'observaciones' => 'UPS de demostración - Sistema de respaldo para servidores',
            ],
        ];

        foreach ($ups as $upsData) {
            Ups::updateOrCreate(
                ['codigo' => $upsData['codigo']],
                $upsData
            );
        }

        $this->command->info('2 UPS de demostración creadas exitosamente.');
    }
}
