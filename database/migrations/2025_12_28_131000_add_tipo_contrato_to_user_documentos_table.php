<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_documentos', function (Blueprint $table) {
            $table->string('tipo_contrato', 40)
                ->nullable()
                ->after('tipo'); // prestacion_servicios | contratista_externo
            $table->index(['user_id', 'tipo_contrato']);
        });
    }

    public function down(): void
    {
        Schema::table('user_documentos', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tipo_contrato']);
            $table->dropColumn('tipo_contrato');
        });
    }
};
