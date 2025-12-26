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
            $table->string('piso', 50)->nullable()->after('zona_id');
            $table->string('tipo', 20)->nullable()->after('piso'); // 'rodillo' o 'normal'
            $table->string('ip', 45)->nullable()->after('tipo'); // IPv4 o IPv6
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropColumn(['piso', 'tipo', 'ip']);
        });
    }
};
