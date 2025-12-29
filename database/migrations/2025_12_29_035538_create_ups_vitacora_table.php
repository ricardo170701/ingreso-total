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
        Schema::create('ups_vitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ups_id')->constrained('ups')->cascadeOnDelete();

            // Imagen subida
            $table->string('imagen')->nullable(); // Ruta de la imagen del panel frontal

            // Indicadores de estado (luces)
            $table->boolean('indicador_normal')->default(false);
            $table->boolean('indicador_battery')->default(false);
            $table->boolean('indicador_bypass')->default(false);
            $table->boolean('indicador_fault')->default(false);

            // Input (entrada)
            $table->decimal('input_voltage', 8, 2)->nullable(); // V
            $table->decimal('input_frequency', 6, 2)->nullable(); // Hz

            // Output (salida)
            $table->decimal('output_voltage', 8, 2)->nullable(); // V
            $table->decimal('output_frequency', 6, 2)->nullable(); // Hz
            $table->decimal('output_power', 10, 2)->nullable(); // W

            // Battery (batería)
            $table->decimal('battery_voltage', 8, 2)->nullable(); // V
            $table->integer('battery_percentage')->nullable(); // %

            // Datos extraídos en JSON (por si hay información adicional)
            $table->json('datos_extraidos')->nullable();

            // Observaciones manuales
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ups_vitacora');
    }
};
