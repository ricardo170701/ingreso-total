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
            $table->foreignId('tarjeta_nfc_id')->nullable()->after('codigo_qr_id')->constrained('tarjetas_nfc')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accesos_registrados', function (Blueprint $table) {
            $table->dropForeign(['tarjeta_nfc_id']);
            $table->dropColumn('tarjeta_nfc_id');
        });
    }
};
