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
        Schema::table('users', function (Blueprint $table) {
            // Permitir email nullable (para visitantes sin correo)
            // Nota: el índice unique existente en email se mantiene; múltiples NULL son permitidos.
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir a NOT NULL (si existen usuarios con email NULL, este down fallará)
            $table->string('email')->nullable(false)->change();
        });
    }
};
