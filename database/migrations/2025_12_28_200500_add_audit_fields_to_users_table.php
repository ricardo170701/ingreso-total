<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('creado_por')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        // Backfill: si ya existe creado_por, úsalo como created_by
        // (evita perder historial al introducir created_by)
        if (Schema::hasColumn('users', 'creado_por') && Schema::hasColumn('users', 'created_by')) {
            DB::statement('UPDATE users SET created_by = creado_por WHERE created_by IS NULL AND creado_por IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('users', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};


