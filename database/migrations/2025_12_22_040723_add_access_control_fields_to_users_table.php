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
            // Identidad / perfil
            $table->string('username', 50)->nullable()->unique()->after('id');
            $table->string('nombre', 100)->nullable()->after('name');
            $table->string('apellido', 100)->nullable()->after('nombre');
            $table->string('departamento', 100)->nullable()->after('apellido');
            $table->text('foto_perfil')->nullable()->after('departamento');
            $table->boolean('activo')->default(true)->after('foto_perfil');
            $table->date('fecha_expiracion')->nullable()->after('activo');
            $table->boolean('es_discapacitado')->default(false)->after('fecha_expiracion');

            // Acceso / permisos
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles')->nullOnDelete();
            $table->foreignId('cargo_id')->nullable()->after('role_id')->constrained('cargos')->nullOnDelete();

            // Auditoría: quién creó el usuario (RRHH / operador)
            $table->foreignId('creado_por')->nullable()->after('cargo_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['cargo_id']);
            $table->dropForeign(['creado_por']);

            $table->dropColumn([
                'username',
                'nombre',
                'apellido',
                'departamento',
                'foto_perfil',
                'activo',
                'fecha_expiracion',
                'es_discapacitado',
                'role_id',
                'cargo_id',
                'creado_por',
            ]);
        });
    }
};
