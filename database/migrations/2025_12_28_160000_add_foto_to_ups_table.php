<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ups', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('serial'); // ruta en storage/public
        });
    }

    public function down(): void
    {
        Schema::table('ups', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
