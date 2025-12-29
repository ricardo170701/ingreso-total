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
        // Eliminar tablas relacionadas que ya no necesitamos
        Schema::dropIfExists('mantenimiento_imagenes');
        Schema::dropIfExists('mantenimiento_defecto');

        // Modificar tabla mantenimientos
        Schema::table('mantenimientos', function (Blueprint $table) {
            // Eliminar columnas que ya no necesitamos
            $table->dropForeign(['usuario_id']);
            $table->dropColumn(['usuario_id', 'otros_defectos', 'observaciones', 'fecha_fin_programada']);

            // Agregar nueva columna 'falla'
            $table->text('falla')->nullable()->after('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->foreignId('usuario_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('otros_defectos')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_fin_programada')->nullable();

            // Eliminar nueva columna
            $table->dropColumn('falla');
        });

        // Recrear tablas eliminadas (estructura básica)
        Schema::create('mantenimiento_defecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos')->cascadeOnDelete();
            $table->foreignId('defecto_id')->constrained('defectos')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('mantenimiento_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos')->cascadeOnDelete();
            $table->string('ruta_imagen');
            $table->integer('orden')->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }
};
