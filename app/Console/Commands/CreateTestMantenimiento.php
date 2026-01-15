<?php

namespace App\Console\Commands;

use App\Models\Mantenimiento;
use App\Models\Puerta;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestMantenimiento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-mantenimiento
                            {--programado-vencido : Crea un mantenimiento tipo programado con fecha límite vencida}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear mantenimiento de prueba (compatible con el módulo actual)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $puerta = Puerta::first();
        $usuario = User::first();

        if (!$puerta || !$usuario) {
            $this->error('No hay puerta o usuario disponible en la BD.');
            return 1;
        }

        $now = now();

        if ($this->option('programado-vencido')) {
            // Vencido: fecha límite en el pasado
            $fechaMantenimiento = $now->copy()->subDays(7)->toDateString();
            $fechaLimite = $now->copy()->subDay()->toDateString();

            $mantenimiento = Mantenimiento::create([
                'puerta_id' => $puerta->id,
                'fecha_mantenimiento' => $fechaMantenimiento,
                'fecha_fin_programada' => $fechaLimite,
                'tipo' => 'programado',
                'descripcion_mantenimiento' => 'MANTENIMIENTO PROGRAMADO (VENCIDO) - Registro de prueba',
                'created_by' => $usuario->id,
            ]);

            $this->info("✅ Mantenimiento PROGRAMADO VENCIDO creado: ID {$mantenimiento->id}");
            $this->line("   - Puerta: {$puerta->nombre} (ID {$puerta->id})");
            $this->line("   - Fecha mantenimiento: {$fechaMantenimiento}");
            $this->line("   - Fecha límite: {$fechaLimite}");
            return 0;
        }

        // Default: realizado (hoy)
        $mantenimiento = Mantenimiento::create([
            'puerta_id' => $puerta->id,
            'fecha_mantenimiento' => $now->toDateString(),
            'fecha_fin_programada' => null,
            'tipo' => 'realizado',
            'descripcion_mantenimiento' => 'MANTENIMIENTO REALIZADO - Registro de prueba',
            'created_by' => $usuario->id,
        ]);

        $this->info("✅ Mantenimiento REALIZADO de prueba creado: ID {$mantenimiento->id}");
        $this->line("   - Puerta: {$puerta->nombre} (ID {$puerta->id})");
        $this->line("   - Fecha: " . $now->toDateString());
        return 0;
    }
}
