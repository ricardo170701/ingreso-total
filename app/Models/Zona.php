<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relación: Una zona tiene muchas puertas
     */
    public function puertas(): HasMany
    {
        return $this->hasMany(Puerta::class);
    }
}
