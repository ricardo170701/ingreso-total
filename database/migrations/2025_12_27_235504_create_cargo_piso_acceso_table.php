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
        Schema::create('cargo_piso_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained('cargos')->cascadeOnDelete();
            $table->foreignId('piso_id')->constrained('pisos')->cascadeOnDelete();

            // Reglas de horario/vigencia (aplican a cargo->piso)
            $table->time('hora_inicio')->nullable(); // null = siempre
            $table->time('hora_fin')->nullable();
            $table->string('dias_semana', 20)->default('1,2,3,4,5,6,7'); // 1=Lunes ... 7=Domingo
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['cargo_id', 'piso_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_piso_acceso');
    }
};
