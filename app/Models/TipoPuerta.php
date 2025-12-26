<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPuerta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación: Un tipo de puerta tiene muchas puertas
     */
    public function puertas(): HasMany
    {
        return $this->hasMany(Puerta::class, 'tipo_puerta_id');
    }
}
