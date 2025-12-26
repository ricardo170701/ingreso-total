<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\TipoPuerta;
use App\Models\Zona;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PuertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pisos = Piso::all();
        $tiposPuerta = TipoPuerta::all();
        $materiales = Material::all();
        $zonas = Zona::where('activa', true)->get();

        if ($pisos->isEmpty()) {
            $this->command->warn('No hay pisos en la base de datos. Ejecuta PisoSeeder primero.');
            return;
        }

        if ($tiposPuerta->isEmpty()) {
            $this->command->warn('No hay tipos de puerta en la base de datos. Ejecuta TipoPuertaSeeder primero.');
            return;
        }

        if ($materiales->isEmpty()) {
            $this->command->warn('No hay materiales en la base de datos. Ejecuta MaterialSeeder primero.');
            return;
        }

        $nombresPuertas = [
            'Entrada Principal',
            'Salida Principal',
            'Entrada Lateral',
            'Puerta de Emergencia',
            'Acceso Restringido',
            'Puerta de Servicio',
            'Entrada VIP',
            'Puerta de Discapacitados',
            'Acceso Norte',
            'Acceso Sur',
            'Puerta Este',
            'Puerta Oeste',
            'Entrada de Personal',
            'Salida de Emergencia',
        ];

        $ubicaciones = [
            'Lobby Principal',
            'Pasillo Central',
            'Área de Recepción',
            'Sala de Espera',
            'Vestíbulo',
            'Corredor Norte',
            'Corredor Sur',
            'Área de Servicios',
        ];

        $ipBase = '192.168.1';
        $ipCounter = 100;

        foreach ($pisos as $piso) {
            // Crear 1-2 puertas por piso
            $cantidadPuertas = rand(1, 2);

            for ($i = 0; $i < $cantidadPuertas; $i++) {
                $tipoPuerta = $tiposPuerta->random();
                $material = $materiales->random();
                $zona = $zonas->isNotEmpty() ? $zonas->random() : null;

                // Generar nombre más variado
                $nombreBase = $nombresPuertas[array_rand($nombresPuertas)];
                $nombre = $nombreBase . ' - ' . $piso->nombre;

                // Generar código físico más descriptivo
                $prefijoPiso = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($piso->nombre, 0, 3)));
                $codigoFisico = $prefijoPiso . '-P' . str_pad($ipCounter, 3, '0', STR_PAD_LEFT);

                // Generar IPs consecutivas para entrada y salida
                $ipEntrada = $ipBase . '.' . $ipCounter;
                $ipSalida = $ipBase . '.' . ($ipCounter + 1);
                $ipCounter += 2;

                // Variar algunos atributos
                $tiempoApertura = [3, 5, 7, 10][array_rand([3, 5, 7, 10])];
                $requiereDiscapacidad = rand(0, 10) < 2; // 20% de probabilidad
                $activo = rand(0, 10) < 9; // 90% de probabilidad de estar activa

                // Dimensiones típicas de puertas (en cm)
                $alto = [200, 210, 220, 240][array_rand([200, 210, 220, 240])];
                $largo = [80, 90, 100, 110][array_rand([80, 90, 100, 110])];
                $ancho = [4, 5, 6, 8][array_rand([4, 5, 6, 8])];

                // Peso aproximado según material (en kg)
                $pesoBase = match ($material->nombre) {
                    'Vidrio' => 30,
                    'Acero' => 80,
                    'Aluminio' => 25,
                    default => 50,
                };
                $peso = $pesoBase + rand(-10, 20); // Variación del peso

                Puerta::updateOrCreate(
                    ['codigo_fisico' => $codigoFisico],
                    [
                        'zona_id' => $zona?->id,
                        'piso_id' => $piso->id,
                        'tipo_puerta_id' => $tipoPuerta->id,
                        'material_id' => $material->id,
                        'ip_entrada' => $ipEntrada,
                        'ip_salida' => $ipSalida,
                        'tiempo_apertura' => $tiempoApertura,
                        'alto' => $alto,
                        'largo' => $largo,
                        'ancho' => $ancho,
                        'peso' => $peso,
                        'nombre' => $nombre,
                        'ubicacion' => $ubicaciones[array_rand($ubicaciones)],
                        'descripcion' => "Puerta de acceso ubicada en {$piso->nombre}",
                        'codigo_fisico' => $codigoFisico,
                        'requiere_discapacidad' => $requiereDiscapacidad,
                        'activo' => $activo,
                    ]
                );
            }
        }

        $this->command->info('Puertas de prueba creadas exitosamente.');
    }
}
