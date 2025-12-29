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
        Schema::table('codigos_qr', function (Blueprint $table) {
            $table->text('token_encrypted')->nullable()->after('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codigos_qr', function (Blueprint $table) {
            $table->dropColumn('token_encrypted');
        });
    }
};
