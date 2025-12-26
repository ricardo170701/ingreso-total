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
            $table->enum('tipo', ['programado', 'realizado'])->default('realizado')->after('fecha_mantenimiento');
            $table->date('fecha_fin_programada')->nullable()->after('tipo'); // Fecha hasta cuando estará en mantenimiento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'fecha_fin_programada']);
        });
    }
};
