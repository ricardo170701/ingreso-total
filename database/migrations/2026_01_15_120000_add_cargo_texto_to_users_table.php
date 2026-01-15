<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'cargo_texto')) {
            Schema::table('users', function (Blueprint $table) {
                // "Cargo" (texto) solo para registro (no afecta permisología)
                $table->string('cargo_texto', 150)->nullable()->after('cargo_id');
            });
        }

        // Backfill: si el usuario ya tenía un cargo_id (modelo Cargo usado para permisos),
        // copiar su nombre al campo de registro cargo_texto para mantener consistencia.
        // Nota: compatible con MySQL.
        try {
            DB::statement(
                "UPDATE users u
                 JOIN cargos c ON c.id = u.cargo_id
                 SET u.cargo_texto = c.name
                 WHERE u.cargo_texto IS NULL AND u.cargo_id IS NOT NULL"
            );
        } catch (\Throwable $e) {
            // Si falla (otro motor), no interrumpir la migración.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'cargo_texto')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('cargo_texto');
            });
        }
    }
};

