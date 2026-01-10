<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Dependencia extends Model
{
    use HasFactory;

    protected $table = 'dependencias';

    protected $fillable = [
        'nombre',
        'piso_id',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación: Una dependencia pertenece a un piso
     */
    public function piso(): BelongsTo
    {
        return $this->belongsTo(Piso::class);
    }

    /**
     * Relación: Una dependencia tiene muchas secretarías
     */
    public function secretarias(): HasMany
    {
        return $this->hasMany(Secretaria::class);
    }

    /**
     * Relación: Una dependencia tiene muchas gerencias a través de sus secretarías
     */
    public function gerencias(): HasManyThrough
    {
        return $this->hasManyThrough(Gerencia::class, Secretaria::class);
    }
}