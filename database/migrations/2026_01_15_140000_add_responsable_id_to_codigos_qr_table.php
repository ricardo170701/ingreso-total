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
        if (!Schema::hasTable('codigos_qr')) {
            return;
        }

        if (!Schema::hasColumn('codigos_qr', 'responsable_id')) {
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->foreignId('responsable_id')
                    ->nullable()
                    ->after('gerencia_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('codigos_qr')) {
            return;
        }

        if (Schema::hasColumn('codigos_qr', 'responsable_id')) {
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->dropForeign(['responsable_id']);
                $table->dropColumn('responsable_id');
            });
        }
    }
};
