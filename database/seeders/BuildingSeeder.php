<?php

namespace Database\Seeders;

use App\Models\Puerta;
use App\Models\Zona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $zonas = [
                [
                    'codigo' => 'P1',
                    'nombre' => 'Piso 1 - Lobby',
                    'descripcion' => 'Entrada principal y recepción',
                    'nivel_seguridad' => 1,
                ],
                [
                    'codigo' => 'P2',
                    'nombre' => 'Piso 2 - Oficinas',
                    'descripcion' => 'Área de oficinas',
                    'nivel_seguridad' => 2,
                ],
                [
                    'codigo' => 'P3',
                    'nombre' => 'Piso 3 - Dirección',
                    'descripcion' => 'Área restringida',
                    'nivel_seguridad' => 4,
                ],
            ];

            foreach ($zonas as $z) {
                $zona = Zona::query()->updateOrCreate(
                    ['codigo' => $z['codigo']],
                    [
                        'nombre' => $z['nombre'],
                        'descripcion' => $z['descripcion'],
                        'nivel_seguridad' => $z['nivel_seguridad'],
                        'activa' => true,
                    ]
                );

                $puertas = [
                    [
                        'nombre' => "Entrada {$z['codigo']}",
                        'codigo_fisico' => "{$z['codigo']}-ENT",
                        'requiere_discapacidad' => false,
                        'activo' => true,
                    ],
                    [
                        'nombre' => "Puerta Discapacitados {$z['codigo']}",
                        'codigo_fisico' => "{$z['codigo']}-DIS",
                        'requiere_discapacidad' => true,
                        'activo' => true,
                    ],
                ];

                foreach ($puertas as $p) {
                    Puerta::query()->updateOrCreate(
                        ['codigo_fisico' => $p['codigo_fisico']],
                        [
                            'zona_id' => $zona->id,
                            'nombre' => $p['nombre'],
                            'ubicacion' => $zona->nombre,
                            'descripcion' => null,
                            'requiere_discapacidad' => $p['requiere_discapacidad'],
                            'activo' => $p['activo'],
                        ]
                    );
                }
            }
        });
    }
}
