<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarjetas_nfc', function (Blueprint $table) {
            if (!Schema::hasColumn('tarjetas_nfc', 'secretaria_id')) {
                $table->foreignId('secretaria_id')
                    ->nullable()
                    ->constrained('secretarias')
                    ->nullOnDelete()
                    ->after('gerencia_id')
                    ->comment('Secretaría destino (para visitantes)');
            }
        });

        Schema::table('tarjeta_nfc_asignaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('tarjeta_nfc_asignaciones', 'secretaria_id')) {
                $table->foreignId('secretaria_id')
                    ->nullable()
                    ->constrained('secretarias')
                    ->nullOnDelete()
                    ->after('gerencia_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tarjeta_nfc_asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('tarjeta_nfc_asignaciones', 'secretaria_id')) {
                $table->dropForeign(['secretaria_id']);
                $table->dropColumn('secretaria_id');
            }
        });

        Schema::table('tarjetas_nfc', function (Blueprint $table) {
            if (Schema::hasColumn('tarjetas_nfc', 'secretaria_id')) {
                $table->dropForeign(['secretaria_id']);
                $table->dropColumn('secretaria_id');
            }
        });
    }
};
