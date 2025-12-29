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

    /**
     * Relación: Un piso pertenece a muchos cargos (muchos a muchos)
     */
    public function cargos(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Cargo::class, 'cargo_piso_acceso')
            ->withPivot([
                'hora_inicio',
                'hora_fin',
                'dias_semana',
                'fecha_inicio',
                'fecha_fin',
                'activo',
            ])
            ->withTimestamps();
    }
}
