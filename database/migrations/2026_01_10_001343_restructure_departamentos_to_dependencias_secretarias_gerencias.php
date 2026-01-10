<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Estructura: Dependencias -> Secretarías -> Gerencias
     * - Una dependencia puede tener muchas secretarías
     * - Una secretaría puede tener muchas gerencias
     * - Los usuarios se asignan a una gerencia (nivel más específico)
     */
    public function up(): void
    {
        // 1. Renombrar tabla departamentos a dependencias
        Schema::rename('departamentos', 'dependencias');

        // 2. Crear tabla secretarias (las secretarías son el recurso principal, NO dependen de dependencias)
        Schema::create('secretarias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('piso_id')->nullable()->constrained('pisos')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índice para búsquedas
            $table->index(['piso_id', 'activo']);
        });

        // 3. Crear tabla gerencias
        Schema::create('gerencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('secretaria_id')->constrained('secretarias')->cascadeOnDelete();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índice para búsquedas y restricción única: nombre único dentro de una secretaría
            $table->index(['secretaria_id', 'activo']);
            $table->unique(['secretaria_id', 'nombre'], 'secretaria_nombre_unique');
        });

        // 4. Cambiar users.departamento_id a gerencia_id
        // IMPORTANTE: La tabla gerencias ya debe existir antes de crear esta foreign key
        // Primero, verificar si la columna existe antes de hacer cambios
        if (Schema::hasColumn('users', 'departamento_id')) {
            // Eliminar foreign key existente hacia dependencias
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['departamento_id']);
            });
            
            // Eliminar columna antigua departamento_id temporalmente
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('departamento_id');
            });
            
            // Crear nueva columna gerencia_id (la tabla gerencias ya existe en este punto)
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('gerencia_id')
                    ->nullable()
                    ->after('apellido')
                    ->constrained('gerencias')
                    ->nullOnDelete();
            });
        }

        // 5. Cambiar codigos_qr.departamento_id a gerencia_id
        // IMPORTANTE: La tabla gerencias ya debe existir antes de crear esta foreign key
        if (Schema::hasColumn('codigos_qr', 'departamento_id')) {
            // Eliminar foreign key e índice existente hacia dependencias
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->dropForeign(['departamento_id']);
                $table->dropIndex(['departamento_id']);
            });
            
            // Eliminar columna antigua departamento_id temporalmente
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->dropColumn('departamento_id');
            });
            
            // Crear nueva columna gerencia_id (la tabla gerencias ya existe en este punto)
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->foreignId('gerencia_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('gerencias')
                    ->nullOnDelete();
                $table->index(['gerencia_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 5. Revertir codigos_qr.gerencia_id a departamento_id
        if (Schema::hasColumn('codigos_qr', 'gerencia_id')) {
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->dropForeign(['gerencia_id']);
                $table->dropIndex(['gerencia_id']);
            });
            
            // Crear columna departamento_id
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->foreignId('departamento_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('dependencias')
                    ->nullOnDelete();
                $table->index(['departamento_id']);
            });
            
            // Eliminar columna gerencia_id
            Schema::table('codigos_qr', function (Blueprint $table) {
                $table->dropColumn('gerencia_id');
            });
        }

        // 4. Revertir users.gerencia_id a departamento_id
        if (Schema::hasColumn('users', 'gerencia_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['gerencia_id']);
            });
            
            // Crear columna departamento_id
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('departamento_id')
                    ->nullable()
                    ->after('apellido')
                    ->constrained('dependencias')
                    ->nullOnDelete();
            });
            
            // Eliminar columna gerencia_id
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('gerencia_id');
            });
        }

        // 3. Eliminar tabla gerencias
        Schema::dropIfExists('gerencias');

        // 2. Eliminar tabla secretarias
        Schema::dropIfExists('secretarias');

        // 1. Renombrar tabla dependencias de vuelta a departamentos
        Schema::rename('dependencias', 'departamentos');
    }
};