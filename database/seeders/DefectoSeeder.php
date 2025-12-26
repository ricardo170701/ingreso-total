<?php

namespace Database\Seeders;

use App\Models\Defecto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defectos = [
            ['nombre' => 'Equipo estado interno', 'codigo' => 'equipo_estado_interno'],
            ['nombre' => 'Accesorios norma', 'codigo' => 'accesorios_norma'],
            ['nombre' => 'Mantenimiento', 'codigo' => 'mantenimiento'],
            ['nombre' => 'Correcta instalación', 'codigo' => 'correcta_instalacion'],
            ['nombre' => 'Correcta programación', 'codigo' => 'correcta_programacion'],
            ['nombre' => 'Puerta en estado óptimo', 'codigo' => 'puerta_estado_optimo'],
            ['nombre' => 'Cuenta con instructivos', 'codigo' => 'cuenta_instructivos'],
            ['nombre' => 'Pintura o señalización', 'codigo' => 'pintura_senalizacion'],
        ];

        foreach ($defectos as $defecto) {
            Defecto::updateOrCreate(
                ['codigo' => $defecto['codigo']],
                [
                    'nombre' => $defecto['nombre'],
                    'codigo' => $defecto['codigo'],
                    'nivel_gravedad' => 0, // No se usa en el defecto base
                    'activo' => true,
                ]
            );
        }
    }
}
