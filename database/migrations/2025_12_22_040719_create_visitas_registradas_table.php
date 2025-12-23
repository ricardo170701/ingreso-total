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
        Schema::create('visitas_registradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('empleado_anfitrion')->constrained('users')->cascadeOnDelete();
            $table->foreignId('operador_registro')->constrained('users')->cascadeOnDelete();

            $table->text('motivo_visita')->nullable();
            $table->dateTime('fecha_entrada_estimada')->nullable();
            $table->dateTime('fecha_salida_estimada')->nullable();
            $table->dateTime('fecha_entrada_real')->nullable();
            $table->dateTime('fecha_salida_real')->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente | activa | finalizada | cancelada
            $table->boolean('qr_generado')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas_registradas');
    }
};
