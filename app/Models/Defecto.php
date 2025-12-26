<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Defecto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'nivel_gravedad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'nivel_gravedad' => 'integer',
    ];

    /**
     * Relación: Un defecto puede estar en muchos mantenimientos
     */
    public function mantenimientos(): BelongsToMany
    {
        return $this->belongsToMany(Mantenimiento::class, 'mantenimiento_defecto')
            ->withTimestamps();
    }
}
