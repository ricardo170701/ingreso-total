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
        Schema::create('mantenimiento_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos')->cascadeOnDelete();
            $table->string('ruta_documento'); // Ruta del PDF en storage
            $table->string('nombre_original')->nullable(); // Nombre original del archivo
            $table->integer('orden')->default(0); // Para ordenar los documentos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_documentos');
    }
};
