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
        Schema::table('departamentos', function (Blueprint $table) {
            // Verificar si las columnas no existen antes de agregarlas
            if (!Schema::hasColumn('departamentos', 'nombre')) {
                $table->string('nombre', 100)->after('id');
            }
            if (!Schema::hasColumn('departamentos', 'piso_id')) {
                $table->foreignId('piso_id')->nullable()->after('nombre')->constrained('pisos')->nullOnDelete();
            }
            if (!Schema::hasColumn('departamentos', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('piso_id');
            }
            if (!Schema::hasColumn('departamentos', 'activo')) {
                $table->boolean('activo')->default(true)->after('descripcion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departamentos', function (Blueprint $table) {
            if (Schema::hasColumn('departamentos', 'piso_id')) {
                $table->dropForeign(['piso_id']);
            }
            $table->dropColumn(['nombre', 'piso_id', 'descripcion', 'activo']);
        });
    }
};
