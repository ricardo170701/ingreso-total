<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarjetas_nfc', function (Blueprint $table) {
            if (!Schema::hasColumn('tarjetas_nfc', 'responsable_id')) {
                $table->foreignId('responsable_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('gerencia_id')
                    ->comment('Responsable (staff) asociado a la visita (solo aplica para visitantes)');
            }
        });

        Schema::table('tarjeta_nfc_asignaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('tarjeta_nfc_asignaciones', 'responsable_id')) {
                $table->foreignId('responsable_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('gerencia_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tarjeta_nfc_asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('tarjeta_nfc_asignaciones', 'responsable_id')) {
                $table->dropForeign(['responsable_id']);
                $table->dropColumn('responsable_id');
            }
        });

        Schema::table('tarjetas_nfc', function (Blueprint $table) {
            if (Schema::hasColumn('tarjetas_nfc', 'responsable_id')) {
                $table->dropForeign(['responsable_id']);
                $table->dropColumn('responsable_id');
            }
        });
    }
};

