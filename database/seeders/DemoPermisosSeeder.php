<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Puerta;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Cargos de ejemplo
            $cargoTrabajador = Cargo::query()->updateOrCreate(
                ['name' => 'Rol Trabajador'],
                ['description' => 'Acceso básico', 'activo' => true]
            );

            $cargoMedio = Cargo::query()->updateOrCreate(
                ['name' => 'Rol Medio'],
                ['description' => 'Acceso medio', 'activo' => true]
            );

            $puertas = Puerta::query()
                ->whereIn('codigo_fisico', ['P1-ENT', 'P2-ENT', 'P3-ENT', 'P1-DIS', 'P2-DIS', 'P3-DIS'])
                ->get()
                ->keyBy('codigo_fisico');

            // Permisos por cargo con horario (null = siempre)
            $this->upsertCargoPuerta($cargoTrabajador->id, $puertas['P1-ENT']->id);

            $this->upsertCargoPuerta($cargoMedio->id, $puertas['P1-ENT']->id);
            $this->upsertCargoPuerta($cargoMedio->id, $puertas['P2-ENT']->id);

            // Super Admin: darle todo (incluye DIS; se sigue exigiendo user.es_discapacitado en runtime)
            $cargoSuper = Cargo::query()->where('name', 'Super Admin')->first();
            if ($cargoSuper) {
                foreach ($puertas as $puerta) {
                    $this->upsertCargoPuerta($cargoSuper->id, $puerta->id);
                }
            }

            // Usuarios demo (opcionales)
            $this->upsertUser('funcionario@local.test', 'Servidor Público Demo', 'servidor_publico', $cargoTrabajador->id, false);
            $this->upsertUser('visitante@local.test', 'Visitante Demo', 'visitante', $cargoTrabajador->id, false);
            $this->upsertUser('disca@local.test', 'Discapacitado Demo', 'servidor_publico', $cargoMedio->id, true);
        });
    }

    private function upsertCargoPuerta(int $cargoId, int $puertaId): void
    {
        DB::table('cargo_puerta_acceso')->updateOrInsert(
            ['cargo_id' => $cargoId, 'puerta_id' => $puertaId],
            [
                'hora_inicio' => null,
                'hora_fin' => null,
                'dias_semana' => '1,2,3,4,5,6,7',
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'activo' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function upsertUser(string $email, string $name, string $roleName, int $cargoId, bool $esDiscapacitado): void
    {
        $role = Role::query()->where('name', $roleName)->first()
            ?? ($roleName === 'servidor_publico' ? Role::query()->where('name', 'funcionario')->first() : null);

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('demo12345'),
                'role_id' => $role?->id,
                'cargo_id' => $cargoId,
                'activo' => true,
                'es_discapacitado' => $esDiscapacitado,
            ]
        );
    }
}
