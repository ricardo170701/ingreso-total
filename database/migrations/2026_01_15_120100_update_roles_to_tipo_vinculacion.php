<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        // Asegurar visitante
        DB::table('roles')->updateOrInsert(
            ['name' => 'visitante'],
            ['description' => 'Visitante (QR por correo / accesos embebidos)', 'updated_at' => now(), 'created_at' => now()]
        );

        $funcionario = DB::table('roles')->where('name', 'funcionario')->first();
        $servidorPublico = DB::table('roles')->where('name', 'servidor_publico')->first();

        // Renombrar funcionario -> servidor_publico (compatibilidad)
        if ($funcionario && !$servidorPublico) {
            DB::table('roles')->where('id', $funcionario->id)->update([
                'name' => 'servidor_publico',
                'description' => 'Servidor público (permisos por rol)',
                'updated_at' => now(),
            ]);
        } elseif ($funcionario && $servidorPublico) {
            // Si ya existía servidor_publico, mover usuarios y eliminar funcionario
            if (Schema::hasTable('users')) {
                DB::table('users')->where('role_id', $funcionario->id)->update([
                    'role_id' => $servidorPublico->id,
                ]);
            }
            if (Schema::hasTable('role_permission')) {
                DB::table('role_permission')->where('role_id', $funcionario->id)->delete();
            }
            DB::table('roles')->where('id', $funcionario->id)->delete();
        } else {
            // Asegurar servidor_publico
            DB::table('roles')->updateOrInsert(
                ['name' => 'servidor_publico'],
                ['description' => 'Servidor público (permisos por rol)', 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Asegurar contratista
        DB::table('roles')->updateOrInsert(
            ['name' => 'contratista'],
            ['description' => 'Contratista (mismas reglas que servidor público)', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        // Eliminar contratista (reasignar a servidor_publico si aplica)
        $contratista = DB::table('roles')->where('name', 'contratista')->first();
        $servidorPublico = DB::table('roles')->where('name', 'servidor_publico')->first();

        if ($contratista) {
            if ($servidorPublico && Schema::hasTable('users')) {
                DB::table('users')->where('role_id', $contratista->id)->update([
                    'role_id' => $servidorPublico->id,
                ]);
            }
            if (Schema::hasTable('role_permission')) {
                DB::table('role_permission')->where('role_id', $contratista->id)->delete();
            }
            DB::table('roles')->where('id', $contratista->id)->delete();
        }

        // Revertir servidor_publico -> funcionario si no existe funcionario
        $funcionario = DB::table('roles')->where('name', 'funcionario')->first();
        $servidorPublico = DB::table('roles')->where('name', 'servidor_publico')->first();
        if (!$funcionario && $servidorPublico) {
            DB::table('roles')->where('id', $servidorPublico->id)->update([
                'name' => 'funcionario',
                'description' => 'Usuario interno (permisos por cargo)',
                'updated_at' => now(),
            ]);
        }
    }
};

