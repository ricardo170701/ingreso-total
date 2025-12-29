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
        Schema::create('ups_mantenimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ups_id')->constrained('ups')->cascadeOnDelete();

            $table->date('fecha_mantenimiento');
            $table->enum('tipo', ['realizado', 'programado'])->default('realizado');
            $table->date('fecha_fin_programada')->nullable();

            $table->text('descripcion')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ups_mantenimientos');
    }
};


