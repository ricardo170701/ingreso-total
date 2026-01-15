<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Permission;
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
            // Roles del sistema (bandera de tipo de vinculación)
            $roles = [
                ['name' => 'servidor_publico', 'description' => 'Servidor público (permisos por rol)'],
                ['name' => 'contratista', 'description' => 'Contratista (mismas reglas que servidor público)'],
                ['name' => 'visitante', 'description' => 'Visitante (QR por correo / accesos embebidos)'],
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

            // Asegurar que el cargo Super Admin tenga todos los permisos del sistema
            $allPermissionIds = Permission::query()
                ->where('activo', true)
                ->pluck('id');

            if ($allPermissionIds->isNotEmpty()) {
                $cargoSuper->permissions()->sync($allPermissionIds);
            }

            // Usuario inicial (configurable por .env)
            $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@local.test');
            $adminPassword = env('SEED_ADMIN_PASSWORD', 'admin12345');

            $roleServidorPublico = Role::query()->where('name', 'servidor_publico')->first()
                ?? Role::query()->where('name', 'funcionario')->first(); // compatibilidad

            // Nota: usamos el modelo User (tabla users). Si luego quieres separar "usuarios" de "users",
            // lo ajustamos con otra migración.
            User::query()->updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'password' => Hash::make($adminPassword),
                    'role_id' => $roleServidorPublico?->id,
                    'cargo_id' => $cargoSuper->id,
                    'activo' => true,
                ]
            );
        });
    }
}
