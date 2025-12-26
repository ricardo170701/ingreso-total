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
        Schema::table('users', function (Blueprint $table) {
            // Primero crear la columna departamento_id
            $table->foreignId('departamento_id')->nullable()->after('apellido')->constrained('departamentos')->nullOnDelete();
        });

        // Migrar datos existentes si hay (opcional, solo si hay datos)
        // Por ahora solo creamos la columna, los datos se migrarán manualmente si es necesario

        Schema::table('users', function (Blueprint $table) {
            // Eliminar la columna antigua departamento (string)
            $table->dropColumn('departamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restaurar columna string
            $table->string('departamento', 100)->nullable()->after('apellido');
            // Eliminar foreign key y columna
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};
