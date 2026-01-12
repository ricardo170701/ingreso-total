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
        Schema::table('puertas', function (Blueprint $table) {
            $table->string('codigo_fisico_salida', 50)->nullable()->unique()->after('codigo_fisico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropUnique(['codigo_fisico_salida']);
            $table->dropColumn('codigo_fisico_salida');
        });
    }
};

