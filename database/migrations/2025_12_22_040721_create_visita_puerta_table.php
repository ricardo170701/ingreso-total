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
        Schema::create('visita_puerta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visita_id')->constrained('visitas_registradas')->cascadeOnDelete();
            $table->foreignId('puerta_id')->constrained('puertas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['visita_id', 'puerta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visita_puerta');
    }
};
