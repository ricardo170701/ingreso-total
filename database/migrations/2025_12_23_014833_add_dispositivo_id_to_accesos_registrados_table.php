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
        Schema::table('accesos_registrados', function (Blueprint $table) {
            $table->string('dispositivo_id', 80)->nullable()->after('lector_id');
            $table->index('dispositivo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accesos_registrados', function (Blueprint $table) {
            $table->dropIndex(['dispositivo_id']);
            $table->dropColumn('dispositivo_id');
        });
    }
};
