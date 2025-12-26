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
        Schema::table('mantenimiento_defecto', function (Blueprint $table) {
            $table->integer('nivel_gravedad')->default(0)->after('defecto_id'); // 0=sin defecto, 1=ligero, 2=grave, 3=muy grave
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimiento_defecto', function (Blueprint $table) {
            $table->dropColumn('nivel_gravedad');
        });
    }
};
