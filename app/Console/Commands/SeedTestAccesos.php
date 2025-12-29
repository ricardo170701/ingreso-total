<?php

namespace App\Console\Commands;

use App\Models\Acceso;
use App\Models\Puerta;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeedTestAccesos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:seed-accesos
                            {--days=30 : Cantidad de días hacia atrás para generar accesos}
                            {--per-day=40 : Accesos promedio por día (aprox)}
                            {--deny-rate=0.15 : Proporción de accesos denegados (0-1)}
                            {--only-activos : Usar solo usuarios/puertas activas}
                            {--truncate : Borrar accesos_registrados antes de insertar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera accesos ficticios en accesos_registrados para visualizar gráficos del dashboard';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $perDay = (int) $this->option('per-day');
        $denyRate = (float) $this->option('deny-rate');
        $onlyActivos = (bool) $this->option('only-activos');
        $truncate = (bool) $this->option('truncate');

        $days = max(1, min(365, $days));
        $perDay = max(1, min(5000, $perDay));
        $denyRate = max(0.0, min(1.0, $denyRate));

        $puertasQuery = Puerta::query();
        $usersQuery = User::query();

        if ($onlyActivos) {
            $puertasQuery->where('activo', true);
            $usersQuery->where('activo', true);
        }

        $puertas = $puertasQuery->get(['id', 'nombre']);
        $users = $usersQuery->get(['id', 'name', 'email']);

        if ($puertas->isEmpty()) {
            $this->error('No hay puertas disponibles para generar accesos.');
            return 1;
        }

        if ($users->isEmpty()) {
            $this->error('No hay usuarios disponibles para generar accesos.');
            return 1;
        }

        if ($truncate) {
            $this->warn('⚠️  Truncando accesos_registrados...');
            DB::table('accesos_registrados')->truncate();
        }

        $now = now();
        $start = $now->copy()->subDays($days)->startOfDay();

        // Para que el gráfico por hora tenga buena data, generamos más densidad en las últimas 24h.
        $boostLast24h = true;

        $totalToInsert = 0;
        $rows = [];

        // Insert en batches para performance
        $batchSize = 2000;

        for ($d = 0; $d < $days; $d++) {
            $day = $start->copy()->addDays($d);

            // Variación diaria (±35%)
            $dayCount = (int) round($perDay * (0.65 + (mt_rand(0, 70) / 100)));
            $dayCount = max(1, $dayCount);

            for ($i = 0; $i < $dayCount; $i++) {
                $isDenied = (mt_rand(1, 1000) / 1000) < $denyRate;

                // Distribución por horas (picos 7-9, 12-14, 17-19)
                $hour = $this->randomHourWeighted();

                // Más ruido en última 24h
                if ($boostLast24h && $day->isSameDay($now)) {
                    $hour = (int) $now->copy()->subHours(mt_rand(0, 23))->format('G');
                }

                $minute = mt_rand(0, 59);
                $second = mt_rand(0, 59);
                $fechaAcceso = $day->copy()->setTime($hour, $minute, $second);

                // Puerta/usuario aleatorios
                $puerta = $puertas->random();
                $user = $users->random();

                $tipoEvento = $isDenied ? 'denegado' : (mt_rand(0, 1) ? 'entrada' : 'salida');

                $rows[] = [
                    'user_id' => $isDenied && mt_rand(0, 100) < 10 ? null : $user->id, // algunos denegados sin user
                    'puerta_id' => $puerta->id,
                    'codigo_qr_id' => null,
                    'tipo_evento' => $tipoEvento,
                    'fecha_acceso' => $fechaAcceso,
                    'permitido' => !$isDenied,
                    'lector_id' => 'SIM-' . $puerta->id,
                    'dispositivo_id' => 'SIM-DEVICE-' . $puerta->id,
                    'ubicacion_lector' => 'simulado',
                    'motivo_denegacion' => $isDenied ? $this->randomMotivoDenegacion() : null,
                    'observaciones' => null,
                    'fotografia_captura' => null,
                    'temperatura' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $totalToInsert++;

                if (count($rows) >= $batchSize) {
                    DB::table('accesos_registrados')->insert($rows);
                    $rows = [];
                }
            }
        }

        if (!empty($rows)) {
            DB::table('accesos_registrados')->insert($rows);
        }

        $count = Acceso::query()->count();
        $this->info("✅ Accesos de prueba generados: {$totalToInsert}");
        $this->line("📌 Total en BD (accesos_registrados): {$count}");
        $this->line("Puertas usadas: {$puertas->count()} | Usuarios usados: {$users->count()}");

        return 0;
    }

    private function randomHourWeighted(): int
    {
        // Pesos aproximados por hora (0-23). Picos 7-9, 12-14, 17-19.
        $weights = [
            0 => 1,
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 3,
            7 => 7,
            8 => 8,
            9 => 6,
            10 => 3,
            11 => 3,
            12 => 6,
            13 => 6,
            14 => 4,
            15 => 3,
            16 => 4,
            17 => 7,
            18 => 8,
            19 => 6,
            20 => 3,
            21 => 2,
            22 => 2,
            23 => 1,
        ];

        $sum = array_sum($weights);
        $r = mt_rand(1, $sum);
        $acc = 0;
        foreach ($weights as $hour => $w) {
            $acc += $w;
            if ($r <= $acc) {
                return (int) $hour;
            }
        }
        return 12;
    }

    private function randomMotivoDenegacion(): string
    {
        $motivos = [
            'QR expirado',
            'QR inválido',
            'Sin permisos para la puerta',
            'Horario no permitido',
            'Usuario inactivo',
        ];

        return $motivos[array_rand($motivos)];
    }
}
