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
        // Verificar si la columna existe antes de eliminarla
        if (!Schema::hasColumn('ups', 'fecha')) {
            return;
        }

        Schema::table('ups', function (Blueprint $table) {
            $table->dropColumn('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si la columna ya existe antes de agregarla
        if (Schema::hasColumn('ups', 'fecha')) {
            return;
        }

        Schema::table('ups', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('codigo');
        });
    }
};
