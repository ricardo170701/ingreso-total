<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ups_vitacora', function (Blueprint $table) {
            $table->decimal('temperatura', 6, 2)->nullable()->after('battery_percentage'); // °C
            $table->integer('battery_tiempo_respaldo')->nullable()->after('battery_percentage'); // minutos
            $table->integer('battery_tiempo_descarga')->nullable()->after('battery_tiempo_respaldo'); // minutos
            $table->string('battery_estado', 50)->nullable()->after('battery_tiempo_descarga'); // charging, discharging, standby
        });
    }

    public function down(): void
    {
        Schema::table('ups_vitacora', function (Blueprint $table) {
            $table->dropColumn(['temperatura', 'battery_tiempo_respaldo', 'battery_tiempo_descarga', 'battery_estado']);
        });
    }
};
