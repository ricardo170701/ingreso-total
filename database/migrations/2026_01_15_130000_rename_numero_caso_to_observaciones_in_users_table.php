<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'numero_caso') && !Schema::hasColumn('users', 'observaciones')) {
            // MySQL no soporta renameColumn directamente, usar ALTER TABLE
            DB::statement('ALTER TABLE `users` CHANGE `numero_caso` `observaciones` TEXT NULL');
        } elseif (!Schema::hasColumn('users', 'observaciones')) {
            // Si no existe numero_caso, crear observaciones directamente
            Schema::table('users', function (Blueprint $table) {
                $table->text('observaciones')->nullable()->after('n_identidad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'observaciones') && !Schema::hasColumn('users', 'numero_caso')) {
            // MySQL: revertir usando ALTER TABLE
            DB::statement('ALTER TABLE `users` CHANGE `observaciones` `numero_caso` VARCHAR(100) NULL');
        }
    }
};
