<?php

namespace Database\Seeders;

use App\Models\Piso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pisos = [
            ['nombre' => 'Subterráneo', 'orden' => 0],
            ['nombre' => 'Mezzanina', 'orden' => 1],
            ['nombre' => 'Piso 1', 'orden' => 2],
            ['nombre' => 'Piso 2', 'orden' => 3],
            ['nombre' => 'Piso 3', 'orden' => 4],
            ['nombre' => 'Piso 4', 'orden' => 5],
            ['nombre' => 'Piso 5', 'orden' => 6],
            ['nombre' => 'Piso 6', 'orden' => 7],
            ['nombre' => 'Piso 7', 'orden' => 8],
            ['nombre' => 'Piso 8', 'orden' => 9],
        ];

        foreach ($pisos as $piso) {
            Piso::updateOrCreate(
                ['nombre' => $piso['nombre']],
                $piso
            );
        }
    }
}
