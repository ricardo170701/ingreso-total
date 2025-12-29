<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ups_mantenimiento_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ups_mantenimiento_id')->constrained('ups_mantenimientos')->cascadeOnDelete();
            $table->string('ruta_documento'); // Ruta del PDF en storage
            $table->string('nombre_original')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ups_mantenimiento_documentos');
    }
};


