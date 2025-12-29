<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar el cargo "super user"
        $cargo = Cargo::query()->where('name', 'super user')->first();

        if (!$cargo) {
            $this->command->error('⚠ El cargo "super user" no existe. Ejecuta primero: php artisan db:seed --class=SuperUserCargoSeeder');
            return;
        }

        // Buscar el rol "funcionario" (por defecto para usuarios internos)
        $role = Role::query()->where('name', 'funcionario')->first();

        if (!$role) {
            $this->command->error('⚠ El rol "funcionario" no existe. Ejecuta primero: php artisan db:seed --class=AccessControlSeeder');
            return;
        }

        // Configuración del usuario (puede ser configurada por .env)
        $email = env('SEED_SUPER_USER_EMAIL', 'superuser@local.test');
        $password = env('SEED_SUPER_USER_PASSWORD', 'superuser12345');
        $name = env('SEED_SUPER_USER_NAME', 'Super Usuario');

        // Crear o actualizar el usuario
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role_id' => $role->id,
                'cargo_id' => $cargo->id,
                'activo' => true,
                'es_discapacitado' => false,
            ]
        );

        $this->command->info("✓ Usuario 'super user' creado/actualizado exitosamente.");
        $this->command->info("  • Email: {$email}");
        $this->command->info("  • Nombre: {$name}");
        $this->command->info("  • Cargo: {$cargo->name}");
        $this->command->info("  • Rol: {$role->name}");
        $this->command->info("  • Password: {$password}");
        $this->command->warn("  ⚠ Recuerda cambiar la contraseña después del primer inicio de sesión.");
    }
}
