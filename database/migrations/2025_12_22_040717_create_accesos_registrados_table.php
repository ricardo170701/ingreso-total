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
        Schema::create('accesos_registrados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('puerta_id')->constrained('puertas')->cascadeOnDelete();
            $table->foreignId('codigo_qr_id')->nullable()->constrained('codigos_qr')->nullOnDelete();

            $table->string('tipo_evento', 20)->default('entrada'); // entrada | salida | denegado
            $table->timestamp('fecha_acceso')->useCurrent();
            $table->boolean('permitido')->default(false);

            $table->string('lector_id', 50)->nullable();
            $table->string('ubicacion_lector', 100)->nullable();
            $table->string('motivo_denegacion', 255)->nullable();

            $table->text('observaciones')->nullable();
            $table->longText('fotografia_captura')->nullable();
            $table->decimal('temperatura', 4, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesos_registrados');
    }
};
