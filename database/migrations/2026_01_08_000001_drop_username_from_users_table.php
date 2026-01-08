<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Si existe el índice UNIQUE por convención, lo eliminamos antes de dropear columna.
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('id');
        });
    }
};

