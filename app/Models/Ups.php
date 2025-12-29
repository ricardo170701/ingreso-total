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
        'nombre',
        'piso_id',
        'ubicacion',
        'marca',
        'modelo',
        'serial',
        'foto',
        'potencia_va',
        'potencia_w',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'potencia_va' => 'integer',
        'potencia_w' => 'integer',
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
