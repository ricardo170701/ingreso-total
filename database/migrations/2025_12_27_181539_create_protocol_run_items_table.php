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
        Schema::create('protocol_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_run_id')->constrained('protocol_runs')->onDelete('cascade');
            $table->foreignId('puerta_id')->constrained('puertas')->onDelete('cascade');
            $table->string('ip')->nullable(); // IP usada (entrada o salida)
            $table->enum('tipo_ip', ['entrada', 'salida'])->default('entrada');
            $table->enum('estado', ['pendiente', 'enviado', 'exitoso', 'fallido', 'timeout'])->default('pendiente');
            $table->integer('intentos')->default(0);
            $table->integer('http_status')->nullable();
            $table->text('respuesta')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_run_items');
    }
};
