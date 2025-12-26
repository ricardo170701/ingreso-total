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
            // Eliminar las columnas string antiguas
            $table->dropColumn(['piso', 'tipo']);

            // Agregar foreign keys
            $table->foreignId('piso_id')->nullable()->after('zona_id')->constrained('pisos')->nullOnDelete();
            $table->foreignId('tipo_puerta_id')->nullable()->after('piso_id')->constrained('tipo_puertas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            // Eliminar foreign keys
            $table->dropForeign(['piso_id']);
            $table->dropForeign(['tipo_puerta_id']);
            $table->dropColumn(['piso_id', 'tipo_puerta_id']);

            // Restaurar columnas string (por si acaso)
            $table->string('piso', 50)->nullable()->after('zona_id');
            $table->string('tipo', 20)->nullable()->after('piso');
        });
    }
};
