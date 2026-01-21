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
        Schema::table('ups', function (Blueprint $table) {
            // Nuevos campos según requerimientos
            $table->string('comp', 100)->nullable()->after('codigo')->comment('Compañía o Compartimiento');
            $table->date('fecha_adquisicion')->nullable()->after('comp')->comment('Fecha de adquisición');
            $table->string('elemt', 100)->nullable()->after('fecha_adquisicion')->comment('Elemento');
            $table->string('ri', 100)->nullable()->after('elemt')->comment('R.I. - Registro Interno');
            $table->string('estado', 50)->nullable()->after('activo')->comment('Estado del UPS (En servicio, Mantenimiento, etc.)');
            
            // Campos de potencia actualizados
            $table->decimal('potencia_kva', 10, 2)->nullable()->after('potencia_va')->comment('Potencia en KVA');
            $table->decimal('potencia_kw', 10, 2)->nullable()->after('potencia_kva')->comment('Potencia en KW');
            
            // Campos de baterías
            $table->integer('cantidad_baterias')->nullable()->after('potencia_kw')->comment('Cantidad de baterías');
            $table->decimal('voltaje_baterias', 8, 2)->nullable()->after('cantidad_baterias')->comment('Voltaje de baterías');
            
            // Ficha técnica (archivo PDF)
            $table->string('ficha_tecnica', 500)->nullable()->after('foto')->comment('Ruta al archivo PDF de ficha técnica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ups', function (Blueprint $table) {
            $table->dropColumn([
                'comp',
                'fecha_adquisicion',
                'elemt',
                'ri',
                'estado',
                'potencia_kva',
                'potencia_kw',
                'cantidad_baterias',
                'voltaje_baterias',
                'ficha_tecnica',
            ]);
        });
    }
};
