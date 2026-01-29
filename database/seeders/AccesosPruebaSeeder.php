<?php

namespace Database\Seeders;

use App\Models\Acceso;
use App\Models\Puerta;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccesosPruebaSeeder extends Seeder
{
    /**
     * Inserta accesos de prueba para poder ver el paginado en Reporte de Ingreso (accesos).
     */
    public function run(): void
    {
        $puertas = Puerta::query()->where('activo', true)->get(['id', 'nombre']);
        $users = User::query()->where('activo', true)->get(['id', 'name']);

        if ($puertas->isEmpty()) {
            $this->command->warn('No hay puertas activas. Ejecuta antes BuildingSeeder o crea puertas.');

            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios activos. Ejecuta antes AccessControlSeeder o crea usuarios.');

            return;
        }

        $tiposPermitidos = ['entrada', 'salida'];
        $motivosDenegacion = [
            'QR expirado',
            'QR inválido',
            'Sin permisos para la puerta',
            'Horario no permitido',
            'Usuario inactivo',
        ];

        // Generar unos 80 accesos para ver varias páginas (ej. 20 por página = 4 páginas)
        $cantidad = 80;
        $now = now();
        $rows = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $diasAtras = random_int(0, 60);
            $fecha = $now->copy()->subDays($diasAtras)
                ->setHour(random_int(6, 20))
                ->setMinute(random_int(0, 59))
                ->setSecond(random_int(0, 59));

            $esDenegado = random_int(1, 10) <= 2; // ~20% denegados
            $tipo = $esDenegado ? 'denegado' : $tiposPermitidos[array_rand($tiposPermitidos)];

            $puerta = $puertas->random();
            $user = $users->random();

            $rows[] = [
                'user_id' => $esDenegado && random_int(1, 10) <= 2 ? null : $user->id,
                'puerta_id' => $puerta->id,
                'codigo_qr_id' => null,
                'tarjeta_nfc_id' => null,
                'tipo_evento' => $tipo,
                'fecha_acceso' => $fecha,
                'permitido' => !$esDenegado,
                'lector_id' => 'SEED-' . $puerta->id,
                'dispositivo_id' => 'SEED-DEV-' . $puerta->id,
                'ubicacion_lector' => 'prueba',
                'motivo_denegacion' => $esDenegado ? $motivosDenegacion[array_rand($motivosDenegacion)] : null,
                'observaciones' => null,
                'fotografia_captura' => null,
                'temperatura' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('accesos_registrados')->insert($rows);

        $total = Acceso::query()->count();
        $this->command->info("Accesos de prueba creados: {$cantidad}. Total en BD: {$total}.");
    }
}
