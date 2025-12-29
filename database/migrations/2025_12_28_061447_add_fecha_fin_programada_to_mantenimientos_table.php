<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            // Fecha límite para mantenimientos programados
            // (si es null, se considerará fecha_mantenimiento como fallback en la lógica de "vencido")
            $table->date('fecha_fin_programada')->nullable()->after('fecha_mantenimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn('fecha_fin_programada');
        });
    }
};
