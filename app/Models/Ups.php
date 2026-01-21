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
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_adquisicion' => 'date',
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

    public function vitacora()
    {
        return $this->hasMany(UpsVitacora::class, 'ups_id')->orderBy('created_at', 'desc');
    }
}
