<?php

namespace Database\Seeders;

use App\Models\TipoPuerta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoPuertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Normal', 'codigo' => 'normal'],
            ['nombre' => 'Rodillo', 'codigo' => 'rodillo'],
        ];

        foreach ($tipos as $tipo) {
            TipoPuerta::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
