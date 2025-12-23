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
        Schema::create('codigo_qr_puerta_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codigo_qr_id')->constrained('codigos_qr')->cascadeOnDelete();
            $table->foreignId('puerta_id')->constrained('puertas')->cascadeOnDelete();

            // Reglas de horario/vigencia (aplican a QR->puerta)
            $table->time('hora_inicio')->nullable(); // null = siempre
            $table->time('hora_fin')->nullable();
            $table->string('dias_semana', 20)->default('1,2,3,4,5,6,7');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['codigo_qr_id', 'puerta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigo_qr_puerta_acceso');
    }
};
