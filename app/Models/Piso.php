<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Relación: Un piso tiene muchas puertas
     */
    public function puertas(): HasMany
    {
        return $this->hasMany(Puerta::class);
    }
}
