<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccessControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Roles del sistema (permisología de la app)
            $roles = [
                ['name' => 'super_usuario', 'description' => 'Puede hacer todo'],
                ['name' => 'operador', 'description' => 'Seguridad: crea QR para visitantes'],
                ['name' => 'rrhh', 'description' => 'Registra funcionarios'],
                ['name' => 'funcionario', 'description' => 'Genera su propio QR según permisos'],
                ['name' => 'visitante', 'description' => 'Recibe QR por correo'],
            ];

            foreach ($roles as $roleData) {
                Role::query()->updateOrCreate(
                    ['name' => $roleData['name']],
                    ['description' => $roleData['description']]
                );
            }

            // Cargo base (permisos físicos: puertas/zonas)
            $cargoSuper = Cargo::query()->updateOrCreate(
                ['name' => 'Super Admin'],
                [
                    'description' => 'Acceso total (asignar puertas/zonas según necesidad)',
                    'activo' => true,
                ]
            );

            // Usuario inicial (configurable por .env)
            $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@local.test');
            $adminPassword = env('SEED_ADMIN_PASSWORD', 'admin12345');

            $roleSuper = Role::query()->where('name', 'super_usuario')->first();

            // Nota: usamos el modelo User (tabla users). Si luego quieres separar "usuarios" de "users",
            // lo ajustamos con otra migración.
            User::query()->updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Super Usuario',
                    'password' => Hash::make($adminPassword),
                    'role_id' => $roleSuper?->id,
                    'cargo_id' => $cargoSuper->id,
                    'activo' => true,
                ]
            );
        });
    }
}
