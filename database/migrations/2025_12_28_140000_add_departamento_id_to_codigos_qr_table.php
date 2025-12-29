<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('codigos_qr', function (Blueprint $table) {
            $table->foreignId('departamento_id')
                ->nullable()
                ->after('user_id')
                ->constrained('departamentos')
                ->nullOnDelete();
            $table->index(['departamento_id']);
        });
    }

    public function down(): void
    {
        Schema::table('codigos_qr', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropIndex(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};
