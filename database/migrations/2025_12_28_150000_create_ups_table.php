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
        Schema::create('ups', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();
            $table->string('nombre');

            $table->foreignId('piso_id')->nullable()->constrained('pisos')->nullOnDelete();

            $table->string('ubicacion')->nullable(); // detalle de ubicación (ej: "Cuarto eléctrico", "Rack 1")
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('serial')->nullable();

            $table->unsignedInteger('potencia_va')->nullable();
            $table->unsignedInteger('potencia_w')->nullable();

            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ups');
    }
};
