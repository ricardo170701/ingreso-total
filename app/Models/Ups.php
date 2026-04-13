<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ups extends Model
{
    use HasFactory;

    protected $table = 'ups';

    protected $fillable = [
        'codigo',
        'comp',
        'fecha_adquisicion',
        'elemt',
        'ri',
        'nombre',
        'piso_id',
        'estado',
        'marca',
        'modelo',
        'serial',
        'ubicacion',
        'foto',
        'ficha_tecnica',
        'potencia_va',
        'potencia_kva',
        'potencia_w',
        'potencia_kw',
        'cantidad_baterias',
        'voltaje_baterias',
        'activo',
        'observaciones',
        'umbrales',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_adquisicion' => 'date',
        'umbrales' => 'array',
        'potencia_va' => 'integer',
        'potencia_w' => 'integer',
        'potencia_kva' => 'decimal:2',
        'potencia_kw' => 'decimal:2',
        'cantidad_baterias' => 'integer',
        'voltaje_baterias' => 'decimal:2',
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class);
    }

    public function mantenimientos()
    {
        return $this->hasMany(UpsMantenimiento::class, 'ups_id')->orderBy('fecha_mantenimiento', 'desc');
    }

    public function bitacora()
    {
        return $this->hasMany(UpsBitacora::class, 'ups_id')->orderBy('created_at', 'desc');
    }

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function normalizeUmbrales(?array $raw): ?array
    {
        if (!$raw) {
            return null;
        }
        $keys = [
            'temperatura',
            'battery_percentage',
            'input_voltage',
            'output_voltage',
            'battery_voltage',
            'output_power',
        ];
        $out = [];
        foreach ($keys as $k) {
            if (!isset($raw[$k]) || !is_array($raw[$k])) {
                continue;
            }
            $min = $raw[$k]['min'] ?? null;
            $max = $raw[$k]['max'] ?? null;
            if ($min === '' || $min === null) {
                $min = null;
            } else {
                $min = (float) $min;
            }
            if ($max === '' || $max === null) {
                $max = null;
            } else {
                $max = (float) $max;
            }
            if ($min === null && $max === null) {
                continue;
            }
            $out[$k] = ['min' => $min, 'max' => $max];
        }

        return $out === [] ? null : $out;
    }
}
