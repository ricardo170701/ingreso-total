<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpsBitacora extends Model
{
    use HasFactory;

    protected $table = 'ups_vitacora';

    protected $fillable = [
        'ups_id',
        'indicador_normal',
        'indicador_battery',
        'indicador_bypass',
        'indicador_fault',
        'input_voltage',
        'input_frequency',
        'output_voltage',
        'output_frequency',
        'output_power',
        'battery_voltage',
        'battery_percentage',
        'battery_tiempo_respaldo',
        'battery_tiempo_descarga',
        'battery_estado',
        'temperatura',
        'datos_extraidos',
        'observaciones',
        'created_by',
    ];

    protected $casts = [
        'indicador_normal' => 'boolean',
        'indicador_battery' => 'boolean',
        'indicador_bypass' => 'boolean',
        'indicador_fault' => 'boolean',
        'input_voltage' => 'decimal:2',
        'input_frequency' => 'decimal:2',
        'output_voltage' => 'decimal:2',
        'output_frequency' => 'decimal:2',
        'output_power' => 'decimal:2',
        'battery_voltage' => 'decimal:2',
        'battery_percentage' => 'integer',
        'battery_tiempo_respaldo' => 'integer',
        'battery_tiempo_descarga' => 'integer',
        'temperatura' => 'decimal:2',
        'datos_extraidos' => 'array',
    ];

    public function ups()
    {
        return $this->belongsTo(Ups::class, 'ups_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function imagenes()
    {
        return $this->hasMany(UpsBitacoraImagen::class, 'ups_vitacora_id')->orderBy('orden');
    }
}
