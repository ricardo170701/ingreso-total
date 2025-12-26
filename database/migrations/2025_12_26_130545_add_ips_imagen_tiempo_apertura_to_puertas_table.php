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
            // Eliminar el campo ip antiguo (si existe) y agregar los nuevos
            $table->dropColumn('ip');

            // IPs para las dos Raspberry Pi (entrada y salida)
            $table->string('ip_entrada', 45)->nullable()->after('tipo_puerta_id');
            $table->string('ip_salida', 45)->nullable()->after('ip_entrada');

            // Imagen de la puerta
            $table->string('imagen')->nullable()->after('ip_salida');

            // Tiempo de apertura en segundos
            $table->integer('tiempo_apertura')->default(5)->after('imagen'); // Por defecto 5 segundos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropColumn(['ip_entrada', 'ip_salida', 'imagen', 'tiempo_apertura']);
            // Restaurar el campo ip original
            $table->string('ip', 45)->nullable()->after('tipo_puerta_id');
        });
    }
};
