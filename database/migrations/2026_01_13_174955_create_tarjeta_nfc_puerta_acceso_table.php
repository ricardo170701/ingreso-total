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
        Schema::create('tarjeta_nfc_puerta_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarjeta_nfc_id')->constrained('tarjetas_nfc')->cascadeOnDelete();
            $table->foreignId('puerta_id')->constrained('puertas')->cascadeOnDelete();

            // Reglas de horario/vigencia (aplican a TarjetaNfc->puerta)
            $table->time('hora_inicio')->nullable(); // null = siempre
            $table->time('hora_fin')->nullable();
            $table->string('dias_semana', 20)->default('1,2,3,4,5,6,7');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['tarjeta_nfc_id', 'puerta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjeta_nfc_puerta_acceso');
    }
};
