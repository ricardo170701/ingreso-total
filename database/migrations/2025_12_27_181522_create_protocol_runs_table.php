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
        Schema::create('protocol_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo')->default('emergencia'); // 'emergencia', 'mantenimiento', etc.
            $table->integer('duration_seconds')->default(900); // 15 minutos por defecto
            $table->enum('estado', ['iniciado', 'en_proceso', 'completado', 'fallido'])->default('iniciado');
            $table->integer('total_puertas')->default(0);
            $table->integer('puertas_exitosas')->default(0);
            $table->integer('puertas_fallidas')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_runs');
    }
};
