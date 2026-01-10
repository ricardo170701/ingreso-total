<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Actualiza la estructura de secretarias:
     * - Elimina dependencia_id (las secretarías ya no dependen de una dependencia)
     * - Agrega piso_id (las secretarías pertenecen directamente a un piso)
     */
    public function up(): void
    {
        // Solo ejecutar si la tabla secretarias existe y tiene dependencia_id
        if (Schema::hasTable('secretarias') && Schema::hasColumn('secretarias', 'dependencia_id')) {
            Schema::table('secretarias', function (Blueprint $table) {
                // Eliminar foreign key e índice de dependencia_id
                $table->dropForeign(['dependencia_id']);
                $table->dropIndex(['dependencia_id', 'activo']);
            });
            
            // Eliminar columna dependencia_id
            Schema::table('secretarias', function (Blueprint $table) {
                $table->dropColumn('dependencia_id');
            });
        }

        // Agregar piso_id si no existe
        if (Schema::hasTable('secretarias') && !Schema::hasColumn('secretarias', 'piso_id')) {
            Schema::table('secretarias', function (Blueprint $table) {
                $table->foreignId('piso_id')
                    ->nullable()
                    ->after('nombre')
                    ->constrained('pisos')
                    ->nullOnDelete();
                $table->index(['piso_id', 'activo']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('secretarias')) {
            // Eliminar piso_id si existe
            if (Schema::hasColumn('secretarias', 'piso_id')) {
                Schema::table('secretarias', function (Blueprint $table) {
                    $table->dropForeign(['piso_id']);
                    $table->dropIndex(['piso_id', 'activo']);
                    $table->dropColumn('piso_id');
                });
            }

            // Restaurar dependencia_id
            // Nota: Solo si la tabla dependencias existe (puede haber sido eliminada)
            if (Schema::hasTable('dependencias') && !Schema::hasColumn('secretarias', 'dependencia_id')) {
                Schema::table('secretarias', function (Blueprint $table) {
                    $table->foreignId('dependencia_id')
                        ->nullable()
                        ->after('nombre')
                        ->constrained('dependencias')
                        ->cascadeOnDelete();
                    $table->index(['dependencia_id', 'activo']);
                });
            }
        }
    }
};