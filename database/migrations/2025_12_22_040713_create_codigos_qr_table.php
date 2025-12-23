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
        Schema::create('codigos_qr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('codigo')->unique(); // token (idealmente hash)
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->timestamp('fecha_expiracion')->nullable();
            $table->boolean('usado')->default(false);
            $table->boolean('activo')->default(true);

            // Opcionales para el flujo (operador/funcionario)
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30)->nullable(); // permanente | temporal | unico_uso
            $table->string('uso_actual', 20)->nullable(); // pendiente | utilizado | expirado
            $table->unsignedSmallInteger('intentos_fallidos')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_qr');
    }
};
