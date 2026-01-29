<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Permisos del sistema (debe ejecutarse primero)
            PermissionSeeder::class,

            // 2. Datos maestros básicos
            PisoSeeder::class,
            TipoPuertaSeeder::class,
            MaterialSeeder::class,

            // 3. Control de acceso (roles, cargo Super Admin, usuario admin)
            AccessControlSeeder::class,

            // 4. Cargo super user con todos los permisos
            SuperUserCargoSeeder::class,

            // 5. Estructura del edificio (zonas y puertas)
            BuildingSeeder::class,

            // 6. Usuario super user
            SuperUserSeeder::class,

            // 7. Accesos de prueba (para reporte de ingreso y paginado)
            AccesosPruebaSeeder::class,
        ]);
    }
}
