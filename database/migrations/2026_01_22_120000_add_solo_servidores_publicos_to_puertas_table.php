<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Solo servidores públicos / proveedores: si está activo, solo pueden acceder
     * usuarios con tipo vinculación servidor_publico, proveedor o funcionario.
     */
    public function up(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->boolean('solo_servidores_publicos')->default(false)->after('requiere_permiso_datacenter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puertas', function (Blueprint $table) {
            $table->dropColumn('solo_servidores_publicos');
        });
    }
};
