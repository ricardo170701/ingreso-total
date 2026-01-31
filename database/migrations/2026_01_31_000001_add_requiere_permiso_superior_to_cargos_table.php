<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Campo para cargos que requieren permiso de seguridad superior al asignar a usuarios.
     */
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->boolean('requiere_permiso_superior')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropColumn('requiere_permiso_superior');
        });
    }
};
