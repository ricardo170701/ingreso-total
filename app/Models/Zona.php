<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    use HasFactory;

    protected $table = 'zonas';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'nivel_seguridad',
        'activa',
        'ubicacion_gps',
    ];

    protected $casts = [
        'nivel_seguridad' => 'integer',
        'activa' => 'boolean',
    ];
}
