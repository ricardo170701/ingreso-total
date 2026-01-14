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
        Schema::create('tarjetas_nfc', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código único de la tarjeta NFC (UID)');
            $table->string('nombre')->nullable()->comment('Nombre descriptivo de la tarjeta (opcional)');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario visitante asignado (opcional, puede estar sin asignar)');
            $table->foreignId('gerencia_id')->nullable()->constrained('gerencias')->nullOnDelete()->comment('Gerencia destino (para visitantes)');
            $table->timestamp('fecha_asignacion')->nullable()->comment('Fecha en que se asignó al usuario');
            $table->timestamp('fecha_expiracion')->nullable()->comment('Fecha de expiración de la tarjeta');
            $table->boolean('activo')->default(true)->comment('Si la tarjeta está activa');
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario que asignó la tarjeta');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjetas_nfc');
    }
};
