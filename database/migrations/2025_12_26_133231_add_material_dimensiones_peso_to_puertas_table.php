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
            $table->foreignId('material_id')->nullable()->after('tipo_puerta_id')->constrained('materials')->nullOnDelete();
            $table->decimal('alto', 8, 2)->nullable()->after('material_id')->comment('Altura en centímetros');
            $table->decimal('largo', 8, 2)->nullable()->after('alto')->comment('Largo en centímetros');
            $table->decimal('ancho', 8, 2)->nullable()->after('largo')->comment('Ancho en centímetros');
            $table->decimal('peso', 8, 2)->nullable()->after('ancho')->comment('Peso en kilogramos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropColumn(['material_id', 'alto', 'largo', 'ancho', 'peso']);
        });
    }
};
