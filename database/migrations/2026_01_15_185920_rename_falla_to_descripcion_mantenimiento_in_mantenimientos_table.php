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
        if (!Schema::hasTable('mantenimientos')) {
            return;
        }

        if (Schema::hasColumn('mantenimientos', 'falla') && !Schema::hasColumn('mantenimientos', 'descripcion_mantenimiento')) {
            DB::statement('ALTER TABLE `mantenimientos` CHANGE `falla` `descripcion_mantenimiento` TEXT NULL');
        } elseif (!Schema::hasColumn('mantenimientos', 'descripcion_mantenimiento')) {
            Schema::table('mantenimientos', function (Blueprint $table) {
                $table->text('descripcion_mantenimiento')->nullable()->after('tipo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('mantenimientos')) {
            return;
        }

        if (Schema::hasColumn('mantenimientos', 'descripcion_mantenimiento') && !Schema::hasColumn('mantenimientos', 'falla')) {
            DB::statement('ALTER TABLE `mantenimientos` CHANGE `descripcion_mantenimiento` `falla` TEXT NULL');
        }
    }
};
