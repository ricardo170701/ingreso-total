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
            $table->boolean('es_oculta')->default(false)->after('requiere_discapacidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropColumn('es_oculta');
        });
    }
};
