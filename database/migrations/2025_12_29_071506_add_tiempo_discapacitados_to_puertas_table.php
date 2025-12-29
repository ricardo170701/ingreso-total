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
        Schema::table('puertas', function (Blueprint $table) {
            // Tiempo de apertura para personas discapacitadas (en segundos)
            $table->integer('tiempo_discapacitados')->nullable()->after('tiempo_apertura')->comment('Tiempo en segundos que la puerta permanece abierta para personas discapacitadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropColumn('tiempo_discapacitados');
        });
    }
};
